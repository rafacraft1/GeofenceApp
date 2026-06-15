<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use App\Models\SiswaModel;

class TrackingApi extends ResourceController
{
    protected $format = 'json';
    protected SiswaModel $siswaModel;
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->siswaModel = model(SiswaModel::class);
        $this->db         = \Config\Database::connect();
    }

    private function catatFraud(int $idSiswa, string $tipeFraud, float $lat, float $lon): bool
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
            'siswa_id'   => $idSiswa,
            'tipe_fraud' => $tipeFraud,
            'lat_fraud'  => $lat,
            'long_fraud' => $lon,
            'user_agent' => $this->request->getUserAgent()->getAgentString() ?? 'Background Service',
            'created_at' => Time::now(getenv('app.appTimezone') ?: 'Asia/Jakarta')->toDateTimeString()
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }

        cache()->delete("siswa_auth_{$idSiswa}");

        return (bool) $isBlocked;
    }

    private function cekAntiFraud(int $idSiswa, int $deviceTimestamp, float $accuracy, int $isMock, float $lat, float $lon): ?\CodeIgniter\HTTP\ResponseInterface
    {
        $serverTime = time();
        $maxTimeDiff = getenv('GEO_MAX_TIME_DIFF') ?: 120;
        $maxAccuracy = getenv('GEO_MAX_ACCURACY') ?: 100;

        if (abs($serverTime - $deviceTimestamp) > $maxTimeDiff) {
            $isBlocked = $this->catatFraud($idSiswa, 'Manipulasi Waktu Perangkat (Selisih > 2 Menit)', $lat, $lon);
            $msg = $isBlocked ? 'Akun diblokir karena indikasi kecurangan berulang.' : 'Waktu perangkat tidak sinkron dengan server. Dilarang mengubah jam HP.';
            return $this->failForbidden($msg);
        }

        if ($accuracy > $maxAccuracy) {
            return $this->failForbidden('Akurasi GPS sangat rendah (' . round($accuracy) . 'm). Silakan cari area terbuka agar presisi.');
        }

        if ($isMock === 1) {
            $isBlocked = $this->catatFraud($idSiswa, 'Fake GPS / Mock Location Terdeteksi (Live Radar)', $lat, $lon);
            $msg = $isBlocked ? 'Akun diblokir karena penggunaan Fake GPS berulang.' : 'Aplikasi Fake GPS terdeteksi secara Real-Time. Matikan segera!';
            return $this->failForbidden($msg);
        }

        return null;
    }

    public function updateLocation()
    {
        $deviceId = $this->request->getHeaderLine('X-Device-ID');
        if (empty($deviceId)) {
            return $this->failUnauthorized('Device ID tidak ditemukan di header.');
        }

        $siswa = cache()->remember("siswa_device_{$deviceId}", 300, function () use ($deviceId) {
            return $this->siswaModel->select('id_siswa, is_blocked')->where('device_id', $deviceId)->first();
        });

        if (!$siswa) {
            return $this->failUnauthorized('Perangkat tidak terdaftar.');
        }

        if ($siswa['is_blocked'] == 1) {
            return $this->failForbidden('Akun Anda diblokir karena terindikasi pelanggaran.');
        }

        $aturanValidasi = [
            'latitude'         => 'required|numeric',
            'longitude'        => 'required|numeric',
            'accuracy'         => 'required|numeric',
            'is_mock'          => 'required|in_list[0,1]',
            'device_timestamp' => 'required|numeric'
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $lat       = (float) $this->request->getPost('latitude');
        $lon       = (float) $this->request->getPost('longitude');
        $accuracy  = (float) $this->request->getPost('accuracy');
        $isMock    = (int) $this->request->getPost('is_mock');
        $timestamp = (int) $this->request->getPost('device_timestamp');

        $fraudCheck = $this->cekAntiFraud((int) $siswa['id_siswa'], $timestamp, $accuracy, $isMock, $lat, $lon);
        if ($fraudCheck) return $fraudCheck;

        $this->siswaModel->update($siswa['id_siswa'], [
            'lat_terakhir'  => $lat,
            'long_terakhir' => $lon,
            'updated_at'    => Time::now(getenv('app.appTimezone') ?: 'Asia/Jakarta')->toDateTimeString()
        ]);

        return $this->respond(['status' => 200, 'message' => 'Lokasi radar berhasil diperbarui.']);
    }
}
