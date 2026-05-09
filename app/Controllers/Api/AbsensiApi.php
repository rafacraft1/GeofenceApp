<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;

class AbsensiApi extends ResourceController
{
    protected $format = 'json';
    protected \CodeIgniter\Database\BaseConnection $db;
    private array|null $siswaCache = null;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        helper(['geo', 'security']);
    }

    private function getSiswaAuth()
    {
        if ($this->siswaCache !== null) return $this->siswaCache;

        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = \str_replace('Bearer ', '', $authHeader);
        if (empty($token)) return null;

        $this->siswaCache = $this->db->table('siswa')->where('api_token', $token)->get()->getRowArray();
        return $this->siswaCache;
    }

    private function getJadwalHariIni(string $tanggalSekarang, string $kodeHari): array
    {
        $libur = $this->db->table('hari_libur')->where('tanggal', $tanggalSekarang)->get()->getRowArray();
        if ($libur) {
            return ['is_libur' => true, 'keterangan' => $libur['keterangan'], 'jam_masuk' => null, 'jam_pulang' => null];
        }

        $jadwal = $this->db->table('jadwal_absen')->where('kode_hari', $kodeHari)->get()->getRowArray();
        if ($jadwal && $jadwal['is_libur'] == 1) {
            return ['is_libur' => true, 'keterangan' => 'Libur (' . $jadwal['nama_hari'] . ')', 'jam_masuk' => null, 'jam_pulang' => null];
        }

        return ['is_libur' => false, 'jam_masuk' => $jadwal['jam_masuk'], 'jam_pulang' => $jadwal['jam_pulang']];
    }

    private function validateAndSaveBase64Image(string $base64String, string $prefix, string $idSiswa): string|false
    {
        $decodedImage = \base64_decode($base64String, true);
        if ($decodedImage === false) return false;

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($decodedImage);

        if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
            return false;
        }

        $extension = ($mimeType === 'image/png') ? 'png' : 'jpg';
        $fileName = $prefix . '_' . $idSiswa . '_' . \time() . '.' . $extension;

        if (\file_put_contents(FCPATH . 'uploads/absensi/' . $fileName, $decodedImage)) {
            return $fileName;
        }
        return false;
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

        // BLOK FAKE GPS (LOG FRAUD DIAKTIFKAN)
        if ($is_mock) {
            $this->db->transStart();

            $this->db->query("UPDATE siswa SET fraud_count = fraud_count + 1 WHERE id_siswa = ?", [$siswa['id_siswa']]);
            $fraudCount = $this->db->table('siswa')->select('fraud_count')->where('id_siswa', $siswa['id_siswa'])->get()->getRow()->fraud_count;

            if ($fraudCount >= 3) {
                $this->db->table('siswa')->where('id_siswa', $siswa['id_siswa'])->update(['is_blocked' => 1]);
            }

            // INSERT LOG FRAUD KE DATABASE
            $this->db->table('log_fraud')->insert([
                'siswa_id'   => $siswa['id_siswa'],
                'tipe_fraud' => 'Fake GPS',
                'lat_fraud'  => $lat,
                'long_fraud' => $lon,
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => \date('Y-m-d H:i:s')
            ]);

            $this->db->transComplete();

            if ($fraudCount >= 3) return $this->failUnauthorized('AKUN DIBLOKIR! Anda terdeteksi menggunakan Fake GPS sebanyak 3 kali.');
            return $this->failForbidden("Fake GPS Terdeteksi! Percobaan Anda tersisa " . (3 - $fraudCount) . " kali lagi.");
        }

        $timezone    = env('app.appTimezone', 'Asia/Jakarta');
        $sekarang    = Time::now($timezone);
        $tanggal_ini = $sekarang->toDateString();
        $kode_hari   = $sekarang->format('N');

        $jadwalHariIni = $this->getJadwalHariIni($tanggal_ini, $kode_hari);

        if ($jadwalHariIni['is_libur']) return $this->failForbidden('Presensi ditolak. Hari ini libur: ' . $jadwalHariIni['keterangan']);

        $jam_masuk_pukul  = Time::parse($tanggal_ini . ' ' . $jadwalHariIni['jam_masuk'], $timezone);
        $jam_pulang_pukul = Time::parse($tanggal_ini . ' ' . $jadwalHariIni['jam_pulang'], $timezone);

        $buka_masuk  = $jam_masuk_pukul->subMinutes(45);
        $tutup_masuk = $jam_pulang_pukul->subMinutes(60);

        if ($sekarang->isBefore($buka_masuk)) return $this->failForbidden('Presensi masuk belum dibuka. Dibuka pukul ' . $buka_masuk->format('H:i'));
        if ($sekarang->isAfter($tutup_masuk)) return $this->failForbidden('Batas waktu presensi masuk sudah lewat.');

        $pengaturan = $this->db->table('pengaturan')->select('latitude_sekolah, longitude_sekolah, radius_meter')->where('id_pengaturan', 1)->get()->getRowArray();
        $jarak = \hitung_jarak_haversine((float)$lat, (float)$lon, (float)$pengaturan['latitude_sekolah'], (float)$pengaturan['longitude_sekolah']);

        if ($jarak > $pengaturan['radius_meter']) return $this->fail('Anda berada ' . \round($jarak) . 'm dari sekolah. Radius maksimal: ' . $pengaturan['radius_meter'] . 'm.');

        $cek = $this->db->table('absensi')->select('id_absensi')->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggal_ini])->get()->getRowArray();
        if ($cek) return $this->failResourceExists('Anda sudah melakukan presensi masuk hari ini.');

        $fileName = $this->validateAndSaveBase64Image($foto, 'masuk', (string)$siswa['id_siswa']);
        if (!$fileName) return $this->failValidationErrors('Format file foto tidak valid.');

        $status = 'Hadir';
        $menit_telat = 0;
        if ($sekarang->isAfter($jam_masuk_pukul)) {
            $status = 'Terlambat';
            $menit_telat = $sekarang->difference($jam_masuk_pukul)->getMinutes();
        }

        $this->db->transStart();
        $this->db->table('absensi')->insert([
            'siswa_id'    => $siswa['id_siswa'],
            'tanggal'     => $tanggal_ini,
            'jam_masuk'   => $sekarang->toTimeString(),
            'status'      => $status,
            'foto_masuk'  => $fileName,
            'is_fake_gps' => 0,
            'menit_telat' => \abs($menit_telat),
            'lat_masuk'   => $lat,
            'long_masuk'  => $lon,
            'created_at'  => \date('Y-m-d H:i:s')
        ]);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) return $this->failServerError('Gagal menyimpan database.');

        return $this->respondCreated(['status' => 200, 'message' => 'Berhasil presensi masuk.']);
    }

    public function pulang()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Sesi berakhir.');

        $lat     = $this->request->getPost('lat');
        $lon     = $this->request->getPost('long');
        $is_mock = $this->request->getPost('is_mock') === 'true';
        $foto    = $this->request->getPost('foto');

        if (!$lat || !$lon || !$foto) return $this->failValidationErrors('Koordinat dan foto selfie wajib dikirim.');

        // BLOK FAKE GPS (LOG FRAUD DIAKTIFKAN)
        if ($is_mock) {
            $this->db->transStart();

            $this->db->query("UPDATE siswa SET fraud_count = fraud_count + 1 WHERE id_siswa = ?", [$siswa['id_siswa']]);
            $fraudCount = $this->db->table('siswa')->select('fraud_count')->where('id_siswa', $siswa['id_siswa'])->get()->getRow()->fraud_count;

            if ($fraudCount >= 3) {
                $this->db->table('siswa')->where('id_siswa', $siswa['id_siswa'])->update(['is_blocked' => 1]);
            }

            // INSERT LOG FRAUD KE DATABASE
            $this->db->table('log_fraud')->insert([
                'siswa_id'   => $siswa['id_siswa'],
                'tipe_fraud' => 'Fake GPS',
                'lat_fraud'  => $lat,
                'long_fraud' => $lon,
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => \date('Y-m-d H:i:s')
            ]);

            $this->db->transComplete();

            if ($fraudCount >= 3) return $this->failUnauthorized('AKUN DIBLOKIR! Terdeteksi Fake GPS sebanyak 3 kali.');
            return $this->failForbidden("Fake GPS Terdeteksi!");
        }

        $timezone    = env('app.appTimezone', 'Asia/Jakarta');
        $sekarang    = Time::now($timezone);
        $tanggal_ini = $sekarang->toDateString();
        $kode_hari   = $sekarang->format('N');

        $jadwalHariIni = $this->getJadwalHariIni($tanggal_ini, $kode_hari);

        if ($jadwalHariIni['is_libur']) return $this->failForbidden('Presensi ditolak. Hari ini libur.');

        $jam_pulang_pukul = Time::parse($tanggal_ini . ' ' . $jadwalHariIni['jam_pulang'], $timezone);
        if ($sekarang->isBefore($jam_pulang_pukul)) {
            return $this->failForbidden('Belum waktunya pulang. Jam pulang hari ini pukul ' . $jam_pulang_pukul->format('H:i'));
        }

        $pengaturan = $this->db->table('pengaturan')->select('latitude_sekolah, longitude_sekolah, radius_meter')->where('id_pengaturan', 1)->get()->getRowArray();
        $jarak = \hitung_jarak_haversine((float)$lat, (float)$lon, (float)$pengaturan['latitude_sekolah'], (float)$pengaturan['longitude_sekolah']);

        if ($jarak > $pengaturan['radius_meter']) return $this->fail('Anda berada ' . \round($jarak) . 'm dari sekolah. Gagal absen pulang.');

        $absen = $this->db->table('absensi')->select('id_absensi, jam_pulang, is_fake_gps')->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggal_ini])->get()->getRowArray();

        if (!$absen) return $this->failNotFound('Anda tidak bisa presensi pulang karena tidak tercatat presensi masuk hari ini.');
        if ($absen['jam_pulang'] != null) return $this->failResourceExists('Anda sudah melakukan presensi pulang hari ini.');

        $fileName = $this->validateAndSaveBase64Image($foto, 'pulang', (string)$siswa['id_siswa']);
        if (!$fileName) return $this->failValidationErrors('Format file foto tidak valid.');

        $this->db->transStart();
        $this->db->table('absensi')->where('id_absensi', $absen['id_absensi'])->update([
            'jam_pulang'  => $sekarang->toTimeString(),
            'foto_pulang' => $fileName,
            'lat_pulang'  => $lat,
            'long_pulang' => $lon,
            'is_fake_gps' => $absen['is_fake_gps'],
            'updated_at'  => \date('Y-m-d H:i:s')
        ]);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) return $this->failServerError('Gagal menyimpan presensi pulang.');

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
