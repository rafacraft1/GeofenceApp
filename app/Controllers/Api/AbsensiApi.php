<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use App\Services\AbsensiService;

class AbsensiApi extends ResourceController
{
    protected $format = 'json';
    protected \CodeIgniter\Database\BaseConnection $db;
    private array|null $siswaCache = null;
    protected AbsensiService $absensiService;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->absensiService = new AbsensiService();
        // Helper security wajib di-load untuk fungsi upload gambar, 
        // Helper geo sudah di-load mandiri di dalam AbsensiService.
        helper(['security']);
    }

    private function getSiswaAuth()
    {
        if ($this->siswaCache !== null) return $this->siswaCache;

        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = \str_replace('Bearer ', '', $authHeader);
        if (empty($token)) return null;

        // Pencarian menggunakan JWT Token ini akan sangat cepat karena sudah di-Index (Fase 1)
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
        if (!$jadwal || $jadwal['is_libur'] == 1) {
            return ['is_libur' => true, 'keterangan' => 'Hari Libur / Akhir Pekan', 'jam_masuk' => null, 'jam_pulang' => null];
        }

        return ['is_libur' => false, 'jam_masuk' => $jadwal['jam_masuk'], 'jam_pulang' => $jadwal['jam_pulang']];
    }

    private function validateAndSaveBase64Image(string $base64String, string $tipe, string $siswaId): ?string
    {
        $imageParts = explode(';base64,', $base64String);
        if (count($imageParts) != 2) return null;

        $imageTypeAux = explode('image/', $imageParts[0]);
        $imageType = $imageTypeAux[1] ?? 'png';
        $imageBase64 = base64_decode($imageParts[1]);

        if (!$imageBase64) return null;

        $fileName = $siswaId . '_' . $tipe . '_' . time() . '.' . $imageType;
        $filePath = FCPATH . 'uploads/absensi/' . $fileName;

        if (file_put_contents($filePath, $imageBase64)) {
            return $fileName;
        }

        return null;
    }

    public function masuk()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Sesi tidak valid atau telah berakhir.');

        $sekarang = Time::now('Asia/Jakarta');
        $tanggalSekarang = $sekarang->toDateString();
        $kodeHari = $sekarang->getDayOfWeek();

        $jadwal = $this->getJadwalHariIni($tanggalSekarang, (string)$kodeHari);

        if ($jadwal['is_libur']) {
            return $this->failForbidden('Hari ini adalah hari libur: ' . $jadwal['keterangan']);
        }

        $absenHariIni = $this->db->table('absensi')->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggalSekarang])->get()->getRowArray();

        if ($absenHariIni) {
            return $this->failResourceExists('Anda sudah melakukan presensi masuk hari ini.');
        }

        $lat       = (float) $this->request->getPost('latitude');
        $lon       = (float) $this->request->getPost('longitude');
        $isFakeGps = (int) $this->request->getPost('is_fake_gps');
        $foto      = (string) $this->request->getPost('foto');

        if (empty($lat) || empty($lon) || empty($foto)) {
            return $this->failValidationErrors('Data koordinat dan foto wajib dikirim.');
        }

        // === 1. IMPLEMENTASI GEOFENCING SERVICE (CLEAN CODE) ===
        $validasi = $this->absensiService->validasiGeofencing($lat, $lon, $isFakeGps);

        if ($validasi['status'] === 'Error') {
            return $this->failServerError($validasi['message']);
        }

        $status     = 'Hadir';
        $keterangan = 'Tepat Waktu';
        $menitTelat = 0;

        // === 2. PENENTUAN STATUS ANTI-FRAUD & TERLAMBAT ===
        if (!$validasi['is_valid']) {
            $status     = 'Manipulasi';
            $keterangan = $validasi['message'];
        } elseif ($sekarang->toTimeString() > $jadwal['jam_masuk']) {
            $status     = 'Terlambat';
            $waktuMasuk = Time::parse($jadwal['jam_masuk']);
            $menitTelat = $sekarang->difference($waktuMasuk)->getMinutes();
            $keterangan = "Terlambat {$menitTelat} Menit";
        }

        $fileName = $this->validateAndSaveBase64Image($foto, 'masuk', (string)$siswa['id_siswa']);
        if (!$fileName) return $this->failValidationErrors('Format file foto tidak valid.');

        $this->db->transStart();
        $this->db->table('absensi')->insert([
            'siswa_id'    => $siswa['id_siswa'],
            'tanggal'     => $tanggalSekarang,
            'jam_masuk'   => $sekarang->toTimeString(),
            'status'      => $status,
            'foto_masuk'  => $fileName,
            'lat_masuk'   => $lat,
            'long_masuk'  => $lon,
            'is_fake_gps' => $isFakeGps,
            'menit_telat' => $menitTelat,
            'keterangan'  => $keterangan,
            'created_at'  => \date('Y-m-d H:i:s')
        ]);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->failServerError('Gagal menyimpan data absensi.');
        }

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Presensi masuk berhasil tercatat.',
            'detail'  => $keterangan
        ]);
    }

    public function pulang()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) return $this->failUnauthorized('Sesi tidak valid.');

        $sekarang = Time::now('Asia/Jakarta');
        $tanggalSekarang = $sekarang->toDateString();

        $absen = $this->db->table('absensi')->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggalSekarang])->get()->getRowArray();

        if (!$absen) return $this->failNotFound('Anda belum melakukan presensi masuk hari ini.');
        if ($absen['jam_pulang'] !== null) return $this->failResourceExists('Anda sudah melakukan presensi pulang hari ini.');

        $lat  = (float) $this->request->getPost('latitude');
        $lon  = (float) $this->request->getPost('longitude');
        $foto = (string) $this->request->getPost('foto');

        $fileName = $this->validateAndSaveBase64Image($foto, 'pulang', (string)$siswa['id_siswa']);
        if (!$fileName) return $this->failValidationErrors('Format file foto tidak valid.');

        $this->db->transStart();
        $this->db->table('absensi')->where('id_absensi', $absen['id_absensi'])->update([
            'jam_pulang'  => $sekarang->toTimeString(),
            'foto_pulang' => $fileName,
            'lat_pulang'  => $lat,
            'long_pulang' => $lon,
            'is_fake_gps' => $absen['is_fake_gps'], // Menjaga status indikasi dari pagi harinya
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

        return $this->respond(['status' => 200, 'data' => $riwayat]);
    }
}
