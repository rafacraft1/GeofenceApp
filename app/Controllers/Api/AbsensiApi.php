<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use CodeIgniter\HTTP\Files\UploadedFile;
use App\Models\AbsensiModel;
use App\Models\SiswaModel;

class AbsensiApi extends ResourceController
{
    protected $format = 'json';
    protected \CodeIgniter\Database\BaseConnection $db;
    protected AbsensiModel $absensiModel;
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->db           = \Config\Database::connect();
        $this->absensiModel = model(AbsensiModel::class);
        $this->siswaModel   = model(SiswaModel::class);
        helper(['security']);
    }

    /**
     * @return array
     */
    private function getSiswaAuth(): array
    {
        return (array) ($this->request->siswaAuth ?? []);
    }

    /**
     * @param UploadedFile|null $file
     * @return string|null
     */
    private function handleFileUpload(?UploadedFile $file): ?string
    {
        if ($file === null || !$file->isValid() || $file->hasMoved()) return null;
        $fileName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/absensi/', $fileName);
        return $fileName;
    }

    /**
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    private function hitungJarakMetres(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /**
     * @param int $idSiswa
     * @param string $jenisPelanggaran
     * @return bool
     */
    private function catatFraud(int $idSiswa, string $jenisPelanggaran): bool
    {
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
            'waktu_kejadian'    => Time::now('Asia/Jakarta')->toDateTimeString()
        ]);

        return (bool) $isBlocked;
    }

    /**
     * @param int $idSiswa
     * @param int $deviceTimestamp
     * @param float $accuracy
     * @param int $isMock
     * @return \CodeIgniter\HTTP\ResponseInterface|null
     */
    private function cekAntiFraud(int $idSiswa, int $deviceTimestamp, float $accuracy, int $isMock): ?\CodeIgniter\HTTP\ResponseInterface
    {
        $serverTime = time();

        if (abs($serverTime - $deviceTimestamp) > 120) {
            $isBlocked = $this->catatFraud($idSiswa, 'Manipulasi Waktu Perangkat (Selisih > 2 Menit)');
            $msg = $isBlocked ? 'Akun diblokir karena indikasi kecurangan berulang.' : 'Waktu perangkat tidak sinkron dengan server. Dilarang mengubah jam HP.';
            return $this->failForbidden($msg);
        }

        if ($accuracy > 100) {
            return $this->failForbidden('Akurasi GPS sangat rendah (' . round($accuracy) . 'm). Silakan cari area terbuka agar presisi.');
        }

        if ($isMock === 1) {
            $isBlocked = $this->catatFraud($idSiswa, 'Penggunaan Fake GPS / Mock Location Terdeteksi');
            $msg = $isBlocked ? 'Akun diblokir karena penggunaan Fake GPS berulang.' : 'Aplikasi Fake GPS terdeteksi. Harap matikan untuk dapat melanjutkan absensi.';
            return $this->failForbidden($msg);
        }

        return null;
    }

    /**
     * @return mixed
     */
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

        $sekarang = Time::now('Asia/Jakarta');
        $kodeHari = (int) $sekarang->format('N');
        $tanggalSekarang = $sekarang->toDateString();
        $currentTime = $sekarang->format('H:i:s');

        $liburNasional = $this->db->table('hari_libur')->where('tanggal', $tanggalSekarang)->get()->getRowArray();
        if ($liburNasional) return $this->failForbidden('Hari ini Libur Nasional: ' . $liburNasional['keterangan']);

        $absenHariIni = $this->absensiModel->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggalSekarang])->first();
        $isDispensasi = ($absenHariIni && $absenHariIni['status'] === 'Dispensasi');

        if ($absenHariIni && !$isDispensasi) return $this->failResourceExists('Anda sudah presensi masuk.');
        if ($isDispensasi && $absenHariIni['jam_masuk'] !== null) return $this->failResourceExists('Bukti lokasi kegiatan sudah dikirim.');

        $aturanZona = $this->siswaModel->getAturanZonaSiswa((string)$siswa['id_siswa'], $kodeHari);
        if (!$aturanZona) return $this->failServerError('Gagal membaca Zona Absensi.');

        if ($aturanZona['is_libur'] == 1) return $this->failForbidden("Hari libur khusus untuk zona " . $aturanZona['nama_zona']);

        if (!$isDispensasi) {
            if ($currentTime < $aturanZona['waktu_buka_absen']) {
                return $this->failForbidden("Absensi di zona {$aturanZona['nama_zona']} dibuka pukul " . date('H:i', strtotime((string)$aturanZona['waktu_buka_absen'])) . " WIB.");
            }
            if ($currentTime > $aturanZona['jam_pulang']) return $this->failForbidden("Sesi absensi masuk sudah ditutup.");
        }

        $isTelat = $currentTime > $aturanZona['jam_masuk'];
        $menitTelat = ($isTelat && !$isDispensasi) ? abs($sekarang->difference(Time::parse($tanggalSekarang . ' ' . $aturanZona['jam_masuk'], 'Asia/Jakarta'))->getMinutes()) : 0;

        $lat = (float) $this->request->getPost('latitude');
        $lon = (float) $this->request->getPost('longitude');

        $status = $isDispensasi ? 'Dispensasi' : 'Hadir';
        $keterangan = $isDispensasi ? 'Hadir di Lokasi Kegiatan' : 'Tepat Waktu';

        if (!$isDispensasi) {
            $jarakMeter = $this->hitungJarakMetres($lat, $lon, (float)$aturanZona['latitude'], (float)$aturanZona['longitude']);

            if ($jarakMeter > (float)$aturanZona['radius']) {
                return $this->failForbidden('Anda berada di luar zona absensi (' . round($jarakMeter) . ' meter dari titik pusat).');
            }

            if ($isTelat) {
                $status = 'Terlambat';
                $keterangan = "Terlambat {$menitTelat} Menit di " . $aturanZona['nama_zona'];
            } else {
                $keterangan = "Tepat Waktu di " . $aturanZona['nama_zona'];
            }
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
            'menit_telat' => $menitTelat,
            'keterangan'  => $keterangan
        ];

        if ($isDispensasi) {
            $this->absensiModel->update($absenHariIni['id_absensi'], $dataAbsen);
            $msg = 'Bukti kehadiran tercatat.';
        } else {
            $dataAbsen['siswa_id'] = $siswa['id_siswa'];
            $dataAbsen['tanggal']  = $tanggalSekarang;
            $dataAbsen['status']   = $status;
            $this->absensiModel->insert($dataAbsen);
            $msg = 'Presensi masuk tercatat.';
        }

        return $this->respondCreated(['status' => 201, 'message' => $msg, 'detail' => $keterangan]);
    }

    /**
     * @return mixed
     */
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

        $sekarang = Time::now('Asia/Jakarta');
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

            if ($aturanZona && $sekarang->format('H:i:s') < $aturanZona['jam_pulang']) {
                return $this->failForbidden("Belum waktunya pulang. Jam pulang untuk zona {$aturanZona['nama_zona']} adalah " . date('H:i', strtotime((string)$aturanZona['jam_pulang'])) . " WIB.");
            }

            $jarakMeter = $this->hitungJarakMetres($lat, $lon, (float)$aturanZona['latitude'], (float)$aturanZona['longitude']);
            if ($jarakMeter > (float)$aturanZona['radius']) {
                return $this->failForbidden('Anda berada di luar zona absensi (' . round($jarakMeter) . ' meter dari titik pusat).');
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

    /**
     * @return mixed
     */
    public function riwayat()
    {
        $siswa = $this->getSiswaAuth();
        $riwayat = $this->absensiModel->where('siswa_id', $siswa['id_siswa'])->orderBy('tanggal', 'DESC')->findAll(30);
        return $this->respond(['status' => 200, 'data' => $riwayat]);
    }
}
