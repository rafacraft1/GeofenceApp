<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use CodeIgniter\HTTP\Files\UploadedFile;
use App\Models\AbsensiModel;
use App\Models\SiswaModel;
use App\Services\AbsensiService;

class AbsensiApi extends ResourceController
{
    protected $format = 'json';
    protected \CodeIgniter\Database\BaseConnection $db;
    protected AbsensiModel $absensiModel;
    protected SiswaModel $siswaModel;
    protected AbsensiService $absensiService;

    public function __construct()
    {
        $this->db             = \Config\Database::connect();
        $this->absensiModel   = model(AbsensiModel::class);
        $this->siswaModel     = model(SiswaModel::class);
        $this->absensiService = new AbsensiService();
        helper(['security']);
    }

    private function getSiswaAuth(): array
    {
        return (array) ($this->request->siswaAuth ?? []);
    }

    private function handleFileUpload(?UploadedFile $file): ?string
    {
        if ($file === null || !$file->isValid() || $file->hasMoved()) return null;

        $fileName = $file->getRandomName();
        // Dikembalikan ke folder publik sesuai instruksi agar dapat diakses controller lain
        $file->move(FCPATH . 'uploads/absensi/', $fileName);

        return $fileName;
    }

    private function catatFraud(int $idSiswa, string $jenisPelanggaran): bool
    {
        $this->db->transStart();

        $siswa = $this->siswaModel->find($idSiswa);
        $newFraudCount = (int) ($siswa['fraud_count'] ?? 0) + 1;
        $isBlocked = ($newFraudCount >= 3) ? 1 : 0;

        $this->siswaModel->update($idSiswa, [
            'fraud_count' => $newFraudCount,
            'is_blocked'  => $isBlocked
        ]);

        $this->db->table('log_fraud')->insert([
            'siswa_id'          => $idSiswa,
            'jenis_pelanggaran' => $jenisPelanggaran,
            'waktu_kejadian'    => Time::now(getenv('app.appTimezone') ?: 'Asia/Jakarta')->toDateTimeString()
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }

        cache()->delete("siswa_auth_{$idSiswa}");

        return (bool) $isBlocked;
    }

    private function cekAntiFraud(int $idSiswa, int $deviceTimestamp, float $accuracy, int $isMock): ?\CodeIgniter\HTTP\ResponseInterface
    {
        $serverTime = time();

        $maxTimeDiff = getenv('GEO_MAX_TIME_DIFF') ?: 120;
        $maxAccuracy = getenv('GEO_MAX_ACCURACY') ?: 100;

        if (abs($serverTime - $deviceTimestamp) > $maxTimeDiff) {
            $isBlocked = $this->catatFraud($idSiswa, 'Manipulasi Waktu Perangkat (Selisih > 2 Menit)');
            $msg = $isBlocked ? 'Akun diblokir karena indikasi kecurangan berulang.' : 'Waktu perangkat tidak sinkron dengan server. Dilarang mengubah jam HP.';
            return $this->failForbidden($msg);
        }

        if ($accuracy > $maxAccuracy) {
            return $this->failForbidden('Akurasi GPS sangat rendah (' . round($accuracy) . 'm). Silakan cari area terbuka agar presisi.');
        }

        if ($isMock === 1) {
            $isBlocked = $this->catatFraud($idSiswa, 'Penggunaan Fake GPS / Mock Location Terdeteksi');
            $msg = $isBlocked ? 'Akun diblokir karena penggunaan Fake GPS berulang.' : 'Aplikasi Fake GPS terdeteksi. Harap matikan untuk dapat melanjutkan absensi.';
            return $this->failForbidden($msg);
        }

        return null;
    }

    public function masuk()
    {
        $siswa = $this->getSiswaAuth();
        $aturanValidasi = [
            'latitude'         => 'required|numeric',
            'longitude'        => 'required|numeric',
            'foto'             => 'uploaded[foto]|is_image[foto]',
            'is_mock'          => 'required|in_list[0,1]',
            'accuracy'         => 'required|numeric',
            'device_timestamp' => 'required|numeric'
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $fraudCheck = $this->cekAntiFraud(
            (int) $siswa['id_siswa'],
            (int) $this->request->getPost('device_timestamp'),
            (float) $this->request->getPost('accuracy'),
            (int) $this->request->getPost('is_mock')
        );

        if ($fraudCheck) return $fraudCheck;

        $timezone = getenv('app.appTimezone') ?: 'Asia/Jakarta';
        $sekarang = Time::now($timezone);
        $kodeHari = (int) $sekarang->format('N');
        $tanggalSekarang = $sekarang->toDateString();

        $liburNasional = $this->db->table('hari_libur')->where('tanggal', $tanggalSekarang)->get()->getRowArray();
        if ($liburNasional) return $this->failForbidden('Hari ini Libur Nasional: ' . $liburNasional['keterangan']);

        $absenHariIni = $this->absensiModel->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggalSekarang])->first();
        $isDispensasi = ($absenHariIni && $absenHariIni['status'] === 'Dispensasi');

        if ($absenHariIni && !$isDispensasi) return $this->failResourceExists('Anda sudah presensi masuk.');
        if ($isDispensasi && $absenHariIni['jam_masuk'] !== null) return $this->failResourceExists('Bukti lokasi kegiatan sudah dikirim.');

        $aturanZona = $this->siswaModel->getAturanZonaSiswa((string)$siswa['id_siswa'], $kodeHari);
        if (!$aturanZona) return $this->failServerError('Gagal membaca Zona Absensi.');

        $lat = (float) $this->request->getPost('latitude');
        $lon = (float) $this->request->getPost('longitude');

        $validasi = $this->absensiService->validasiMasuk($aturanZona, $lat, $lon, $sekarang, $isDispensasi);
        if (!$validasi['status']) {
            return $this->failForbidden($validasi['message']);
        }

        $fileName = $this->handleFileUpload($this->request->getFile('foto'));
        if (!$fileName) return $this->failValidationErrors('Gagal mengunggah foto.');

        $realtimeSiswa = $this->db->table('siswa')->select('kelas_id')->where('id_siswa', $siswa['id_siswa'])->get()->getRowArray();
        $dataAbsen = [
            'kelas_id'    => $realtimeSiswa['kelas_id'] ?? null,
            'jam_masuk'   => $sekarang->toTimeString(),
            'foto_masuk'  => $fileName,
            'lat_masuk'   => $lat,
            'long_masuk'  => $lon,
            'is_fake_gps' => 0,
            'menit_telat' => $validasi['menit_telat'],
            'keterangan'  => $validasi['keterangan']
        ];

        if ($isDispensasi) {
            $this->absensiModel->update($absenHariIni['id_absensi'], $dataAbsen);
            $msg = 'Bukti kehadiran tercatat.';
        } else {
            $dataAbsen['siswa_id'] = $siswa['id_siswa'];
            $dataAbsen['tanggal']  = $tanggalSekarang;
            $dataAbsen['status']   = $validasi['absen_status'];
            $this->absensiModel->insert($dataAbsen);
            $msg = 'Presensi masuk tercatat.';
        }

        return $this->respondCreated(['status' => 201, 'message' => $msg, 'detail' => $validasi['keterangan']]);
    }

    public function pulang()
    {
        $siswa = $this->getSiswaAuth();
        $aturanValidasi = [
            'latitude'         => 'required|numeric',
            'longitude'        => 'required|numeric',
            'foto'             => 'uploaded[foto]|is_image[foto]',
            'is_mock'          => 'required|in_list[0,1]',
            'accuracy'         => 'required|numeric',
            'device_timestamp' => 'required|numeric'
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $fraudCheck = $this->cekAntiFraud(
            (int) $siswa['id_siswa'],
            (int) $this->request->getPost('device_timestamp'),
            (float) $this->request->getPost('accuracy'),
            (int) $this->request->getPost('is_mock')
        );

        if ($fraudCheck) return $fraudCheck;

        $timezone = getenv('app.appTimezone') ?: 'Asia/Jakarta';
        $sekarang = Time::now($timezone);
        $kodeHari = (int) $sekarang->format('N');
        $tanggalSekarang = $sekarang->toDateString();

        $liburNasional = $this->db->table('hari_libur')->where('tanggal', $tanggalSekarang)->get()->getRowArray();
        if ($liburNasional) return $this->failForbidden('Hari ini Libur Nasional: ' . $liburNasional['keterangan']);

        $absen = $this->absensiModel->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggalSekarang])->first();
        if (!$absen) return $this->failNotFound('Anda belum presensi masuk hari ini.');
        if ($absen['jam_pulang'] !== null) return $this->failResourceExists('Anda sudah presensi pulang.');

        $isDispensasi = ($absen['status'] === 'Dispensasi');
        $lat = (float) $this->request->getPost('latitude');
        $lon = (float) $this->request->getPost('longitude');

        if (!$isDispensasi) {
            $aturanZona = $this->siswaModel->getAturanZonaSiswa((string)$siswa['id_siswa'], $kodeHari);
            if (!$aturanZona) return $this->failServerError('Gagal membaca Zona Absensi.');

            $validasi = $this->absensiService->validasiPulang($aturanZona, $lat, $lon, $sekarang, $isDispensasi);
            if (!$validasi['status']) {
                return $this->failForbidden($validasi['message']);
            }
        }

        $fileName = $this->handleFileUpload($this->request->getFile('foto'));
        if (!$fileName) return $this->failValidationErrors('Gagal mengunggah file foto.');

        $this->absensiModel->update($absen['id_absensi'], [
            'jam_pulang'  => $sekarang->toTimeString(),
            'foto_pulang' => $fileName,
            'lat_pulang'  => $lat,
            'long_pulang' => $lon
        ]);

        return $this->respondUpdated(['status' => 200, 'message' => $isDispensasi ? 'Tugas selesai!' : 'Presensi pulang berhasil. Hati-hati di jalan!']);
    }

    public function riwayat()
    {
        $siswa = $this->getSiswaAuth();
        $riwayat = $this->absensiModel->where('siswa_id', $siswa['id_siswa'])->orderBy('tanggal', 'DESC')->findAll(30);
        return $this->respond(['status' => 200, 'data' => $riwayat]);
    }
}
