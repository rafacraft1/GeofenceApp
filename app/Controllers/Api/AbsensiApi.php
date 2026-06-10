<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use CodeIgniter\HTTP\Files\UploadedFile;
use App\Services\AbsensiService;
use App\Models\AbsensiModel;

class AbsensiApi extends ResourceController
{
    protected $format = 'json';
    protected \CodeIgniter\Database\BaseConnection $db;
    protected AbsensiService $absensiService;
    protected AbsensiModel $absensiModel;

    public function __construct()
    {
        $this->db             = \Config\Database::connect();
        $this->absensiService = new AbsensiService();
        $this->absensiModel   = model(AbsensiModel::class);
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
     * @param string $tanggalSekarang
     * @param string $kodeHari
     * @return array
     */
    private function getJadwalHariIni(string $tanggalSekarang, string $kodeHari): array
    {
        $cacheKeyLibur = 'hari_libur_' . $tanggalSekarang;
        $libur = cache()->remember($cacheKeyLibur, 43200, function () use ($tanggalSekarang) {
            return $this->db->table('hari_libur')->where('tanggal', $tanggalSekarang)->get()->getRowArray();
        });

        if ($libur) {
            return ['is_libur' => true, 'keterangan' => $libur['keterangan'], 'jam_masuk' => null, 'jam_pulang' => null];
        }

        $cacheKeyJadwal = 'jadwal_hari_' . $kodeHari;
        $jadwal = cache()->remember($cacheKeyJadwal, 43200, function () use ($kodeHari) {
            return $this->db->table('jadwal_absen')->where('kode_hari', $kodeHari)->get()->getRowArray();
        });

        if (!$jadwal || $jadwal['is_libur'] == 1) {
            return ['is_libur' => true, 'keterangan' => 'Hari Libur / Akhir Pekan', 'jam_masuk' => null, 'jam_pulang' => null];
        }

        return ['is_libur' => false, 'jam_masuk' => $jadwal['jam_masuk'], 'jam_pulang' => $jadwal['jam_pulang']];
    }

    /**
     * @param UploadedFile|null $file
     * @return string|null
     */
    private function handleFileUpload(?UploadedFile $file): ?string
    {
        if ($file === null || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $fileName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/absensi/', $fileName);

        return $fileName;
    }

    /**
     * @return mixed
     */
    public function masuk()
    {
        $siswa = $this->getSiswaAuth();

        $aturanValidasi = [
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'foto'        => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]',
            'is_fake_gps' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $sekarang        = Time::now('Asia/Jakarta');
        $tanggalSekarang = $sekarang->toDateString();
        $kodeHari        = $sekarang->format('N');

        $jadwal = $this->getJadwalHariIni($tanggalSekarang, (string)$kodeHari);
        if ($jadwal['is_libur']) return $this->failForbidden('Hari ini libur: ' . $jadwal['keterangan']);

        $absenHariIni = $this->absensiModel->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggalSekarang])->first();
        $isDispensasi = ($absenHariIni && $absenHariIni['status'] === 'Dispensasi');

        if ($absenHariIni && !$isDispensasi) {
            return $this->failResourceExists('Anda sudah presensi masuk hari ini.');
        }

        if ($isDispensasi && $absenHariIni['jam_masuk'] !== null) {
            return $this->failResourceExists('Anda sudah mengirim bukti tiba di lokasi kegiatan hari ini.');
        }

        $evaluasiWaktu = [];
        if (!$isDispensasi) {
            $evaluasiWaktu = $this->absensiService->evaluasiWaktuMasuk($sekarang, $jadwal['jam_masuk'], $jadwal['jam_pulang']);
            if (!$evaluasiWaktu['status']) return $this->failForbidden($evaluasiWaktu['message']);
        }

        $lat       = (float) $this->request->getPost('latitude');
        $lon       = (float) $this->request->getPost('longitude');
        $isFakeGps = (int) $this->request->getPost('is_fake_gps');

        $status     = $isDispensasi ? 'Dispensasi' : 'Hadir';
        $keterangan = $isDispensasi ? 'Hadir di Lokasi Kegiatan' : 'Tepat Waktu';
        $menitTelat = $evaluasiWaktu['menit_telat'] ?? 0;

        if (!$isDispensasi) {
            $validasiGeo = $this->absensiService->validasiGeofencing($lat, $lon, $isFakeGps);
            if ($validasiGeo['status'] === 'Error') return $this->failServerError($validasiGeo['message']);

            if (!$validasiGeo['is_valid']) {
                $status     = 'Manipulasi';
                $keterangan = $validasiGeo['message'];
            } elseif ($evaluasiWaktu['is_telat']) {
                $status     = 'Terlambat';
                $keterangan = "Terlambat {$menitTelat} Menit";
            }
        } elseif ($isFakeGps) {
            $keterangan = 'Hadir di Lokasi Kegiatan (Fake GPS Terdeteksi)';
        }

        $fileFoto = $this->request->getFile('foto');
        $fileName = $this->handleFileUpload($fileFoto);

        if (!$fileName) return $this->failValidationErrors('Gagal mengunggah file foto atau format tidak valid.');

        $realtimeSiswa = $this->db->table('siswa')->select('kelas_id')->where('id_siswa', $siswa['id_siswa'])->get()->getRowArray();

        $dataAbsen = [
            'kelas_id'    => $realtimeSiswa['kelas_id'] ?? null,
            'jam_masuk'   => $sekarang->toTimeString(),
            'foto_masuk'  => $fileName,
            'lat_masuk'   => $lat,
            'long_masuk'  => $lon,
            'is_fake_gps' => $isFakeGps,
            'menit_telat' => $menitTelat,
            'keterangan'  => $keterangan
        ];

        try {
            if ($isDispensasi) {
                $this->absensiModel->update($absenHariIni['id_absensi'], $dataAbsen);
                $message = 'Bukti kehadiran di lokasi kegiatan berhasil tercatat.';
            } else {
                $dataAbsen['siswa_id'] = $siswa['id_siswa'];
                $dataAbsen['tanggal']  = $tanggalSekarang;
                $dataAbsen['status']   = $status;
                $this->absensiModel->insert($dataAbsen);
                $message = 'Presensi masuk berhasil tercatat.';
            }
            return $this->respondCreated(['status' => 201, 'message' => $message, 'detail' => $keterangan]);
        } catch (\Exception $e) {
            return $this->failServerError('Gagal menyimpan data absensi.');
        }
    }

    /**
     * @return mixed
     */
    public function pulang()
    {
        $siswa = $this->getSiswaAuth();

        $aturanValidasi = [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto'      => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]'
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $sekarang        = Time::now('Asia/Jakarta');
        $tanggalSekarang = $sekarang->toDateString();
        $kodeHari        = $sekarang->format('N');

        $jadwal = $this->getJadwalHariIni($tanggalSekarang, (string)$kodeHari);
        if ($jadwal['is_libur']) return $this->failForbidden('Hari ini libur: ' . $jadwal['keterangan']);

        $absen = $this->absensiModel->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggalSekarang])->first();

        if (!$absen) return $this->failNotFound('Anda belum melakukan presensi masuk hari ini.');
        if ($absen['jam_pulang'] !== null) return $this->failResourceExists('Anda sudah presensi pulang hari ini.');

        $isDispensasi = ($absen['status'] === 'Dispensasi');

        if (!$isDispensasi) {
            $evaluasiPulang = $this->absensiService->evaluasiWaktuPulang($sekarang, $jadwal['jam_pulang']);
            if (!$evaluasiPulang['status']) return $this->failForbidden($evaluasiPulang['message']);
        }

        $fileFoto = $this->request->getFile('foto');
        $fileName = $this->handleFileUpload($fileFoto);

        if (!$fileName) return $this->failValidationErrors('Gagal mengunggah file foto atau format tidak valid.');

        try {
            $this->absensiModel->update($absen['id_absensi'], [
                'jam_pulang'  => $sekarang->toTimeString(),
                'foto_pulang' => $fileName,
                'lat_pulang'  => (float) $this->request->getPost('latitude'),
                'long_pulang' => (float) $this->request->getPost('longitude')
            ]);

            $pesanSukses = $isDispensasi ? 'Tugas selesai, bukti pulang kegiatan berhasil disimpan!' : 'Presensi pulang berhasil. Hati-hati di jalan!';
            return $this->respondUpdated(['status' => 200, 'message' => $pesanSukses]);
        } catch (\Exception $e) {
            return $this->failServerError('Gagal menyimpan presensi pulang.');
        }
    }

    /**
     * @return mixed
     */
    public function riwayat()
    {
        $siswa   = $this->getSiswaAuth();
        $riwayat = $this->absensiModel
            ->where('siswa_id', $siswa['id_siswa'])
            ->orderBy('tanggal', 'DESC')
            ->findAll(30);

        return $this->respond(['status' => 200, 'data' => $riwayat]);
    }
}
