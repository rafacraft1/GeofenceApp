<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;

class AbsensiApi extends ResourceController
{
    protected $format = 'json';

    /** @var \CodeIgniter\Database\BaseConnection */
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        helper(['geo', 'security']); // Pastikan helper geo dan security buatan Anda aman
    }

    private function getSiswaAuth()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = \str_replace('Bearer ', '', $authHeader);
        if (empty($token)) return null;

        return $this->db->table('siswa')->where('api_token', $token)->get()->getRowArray();
    }

    public function masuk()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Sesi berakhir atau token tidak valid.');

        $lat     = $this->request->getPost('lat');
        $lon     = $this->request->getPost('long');
        $is_mock = $this->request->getPost('is_mock') === 'true';
        $foto    = $this->request->getPost('foto');

        if (!$lat || !$lon || !$foto) return $this->failValidationErrors('Koordinat dan foto selfie wajib dikirim.');

        if ($is_mock) {
            $this->db->query("UPDATE siswa SET fraud_count = fraud_count + 1 WHERE id_siswa = ?", [$siswa['id_siswa']]);
            $siswa_cek = $this->db->table('siswa')->where('id_siswa', $siswa['id_siswa'])->get()->getRowArray();
            $sisa = 3 - $siswa_cek['fraud_count'];

            if ($siswa_cek['fraud_count'] >= 3) {
                $this->db->table('siswa')->where('id_siswa', $siswa['id_siswa'])->update(['is_blocked' => 1]);
                return $this->failUnauthorized('AKUN DIBLOKIR! Anda terdeteksi menggunakan Fake GPS sebanyak 3 kali.');
            }
            return $this->failForbidden("Fake GPS Terdeteksi! Percobaan Anda tersisa $sisa kali lagi sebelum diblokir.");
        }

        $sekarang       = Time::now('Asia/Jakarta');
        $tanggal_ini    = $sekarang->toDateString();
        $pengaturan     = $this->db->table('pengaturan')->where('id_pengaturan', 1)->get()->getRowArray();

        $jam_masuk_pukul  = Time::parse($tanggal_ini . ' ' . $pengaturan['jam_masuk'], 'Asia/Jakarta');
        $jam_pulang_pukul = Time::parse($tanggal_ini . ' ' . $pengaturan['jam_pulang'], 'Asia/Jakarta');
        $buka_masuk  = $jam_masuk_pukul->subMinutes(45);
        $tutup_masuk = $jam_pulang_pukul->subMinutes(60);

        if ($sekarang->isBefore($buka_masuk)) return $this->failForbidden('Presensi masuk belum dibuka. Dibuka pukul ' . $buka_masuk->format('H:i'));
        if ($sekarang->isAfter($tutup_masuk)) return $this->failForbidden('Batas waktu presensi masuk sudah lewat.');

        $jarak = \hitung_jarak_haversine($lat, $lon, $pengaturan['latitude_sekolah'], $pengaturan['longitude_sekolah']);
        if ($jarak > $pengaturan['radius_meter']) return $this->fail('Anda berada ' . \round($jarak) . 'm dari sekolah. Radius maksimal: ' . $pengaturan['radius_meter'] . 'm.');

        $cek = $this->db->table('absensi')->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggal_ini])->get()->getRowArray();
        if ($cek) return $this->failResourceExists('Anda sudah melakukan presensi masuk hari ini.');

        // Simpan File Fisik
        $decodedFoto = \base64_decode($foto);
        $fileName = 'masuk_' . $siswa['id_siswa'] . '_' . \time() . '.jpg';
        \file_put_contents(FCPATH . 'uploads/absensi/' . $fileName, $decodedFoto);

        $status = 'Hadir';
        $menit_telat = 0;
        if ($sekarang->isAfter($jam_masuk_pukul)) {
            $status = 'Terlambat';
            $menit_telat = $sekarang->difference($jam_masuk_pukul)->getMinutes();
        }

        $this->db->table('absensi')->insert([
            'siswa_id'    => $siswa['id_siswa'],
            'tanggal'     => $tanggal_ini,
            'jam_masuk'   => $sekarang->toTimeString(),
            'status'      => $status,
            'is_fake_gps' => $is_mock ? 1 : 0,
            'menit_telat' => \abs($menit_telat),
            'lat_masuk'   => $lat,
            'long_masuk'  => $lon,
            'created_at'  => \date('Y-m-d H:i:s')
        ]);

        return $this->respondCreated(['status' => 200, 'message' => 'Berhasil melakukan presensi masuk. Selamat belajar!']);
    }

    public function pulang()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Sesi berakhir.');

        $lat     = $this->request->getPost('lat');
        $lon     = $this->request->getPost('long');
        $is_mock = $this->request->getPost('is_mock') === 'true';
        $foto    = $this->request->getPost('foto');

        if ($is_mock) {
            $this->db->query("UPDATE siswa SET fraud_count = fraud_count + 1 WHERE id_siswa = ?", [$siswa['id_siswa']]);
            $siswa_cek = $this->db->table('siswa')->where('id_siswa', $siswa['id_siswa'])->get()->getRowArray();

            if ($siswa_cek['fraud_count'] >= 3) {
                $this->db->table('siswa')->where('id_siswa', $siswa['id_siswa'])->update(['is_blocked' => 1]);
                return $this->failUnauthorized('AKUN DIBLOKIR! Terdeteksi Fake GPS sebanyak 3 kali.');
            }
            return $this->failForbidden("Fake GPS Terdeteksi!");
        }

        $sekarang    = Time::now('Asia/Jakarta');
        $tanggal_ini = $sekarang->toDateString();

        $absen = $this->db->table('absensi')->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggal_ini])->get()->getRowArray();
        if (!$absen) return $this->failNotFound('Anda tidak bisa presensi pulang karena tidak tercatat presensi masuk hari ini.');
        if ($absen['jam_pulang'] != null) return $this->failResourceExists('Anda sudah melakukan presensi pulang hari ini.');

        // Simpan File Fisik
        $decodedFoto = \base64_decode($foto);
        $fileName = 'pulang_' . $siswa['id_siswa'] . '_' . \time() . '.jpg';
        \file_put_contents(FCPATH . 'uploads/absensi/' . $fileName, $decodedFoto);

        $this->db->table('absensi')->where('id_absensi', $absen['id_absensi'])->update([
            'jam_pulang'  => $sekarang->toTimeString(),
            'lat_pulang'  => $lat,
            'long_pulang' => $lon,
            'is_fake_gps' => $is_mock ? 1 : $absen['is_fake_gps'],
            'updated_at'  => \date('Y-m-d H:i:s')
        ]);

        return $this->respondUpdated(['status' => 200, 'message' => 'Presensi pulang berhasil. Hati-hati di jalan!']);
    }

    public function riwayat()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Sesi tidak valid.');

        $riwayat = $this->db->table('absensi')
            ->where('siswa_id', $siswa['id_siswa'])
            ->orderBy('tanggal', 'DESC')
            ->limit(30)
            ->get()
            ->getResultArray();

        return $this->respond([
            'status'  => 200,
            'message' => 'Data riwayat berhasil ditarik.',
            'data'    => $riwayat
        ]);
    }
}
