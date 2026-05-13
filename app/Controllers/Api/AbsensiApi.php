<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
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
        $this->absensiModel   = new AbsensiModel();
        helper(['security']);
    }

    // --- TAMBAHAN FIX INTELEPHENSE P1014 ---
    private function getSiswaAuth(): array
    {
        /** @var mixed $request */
        $request = $this->request;
        return (array) $request->siswaAuth;
    }
    // ---------------------------------------

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

        $imageBase64 = base64_decode($imageParts[1]);
        if (!$imageBase64) return null;

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageBase64);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/jpg'  => 'jpg'
        ];

        if (!array_key_exists($mimeType, $allowedMimes)) {
            return null;
        }

        $extension = $allowedMimes[$mimeType];
        $fileName  = $siswaId . '_' . $tipe . '_' . time() . '.' . $extension;
        $filePath  = FCPATH . 'uploads/absensi/' . $fileName;

        if (file_put_contents($filePath, $imageBase64)) {
            return $fileName;
        }

        return null;
    }

    public function masuk()
    {
        $siswa = $this->getSiswaAuth();

        $aturanValidasi = [
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'foto'        => 'required',
            'is_fake_gps' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $sekarang        = Time::now('Asia/Jakarta');
        $tanggalSekarang = $sekarang->toDateString();
        $kodeHari        = $sekarang->getDayOfWeek();

        $jadwal = $this->getJadwalHariIni($tanggalSekarang, (string)$kodeHari);
        if ($jadwal['is_libur']) return $this->failForbidden('Hari ini libur: ' . $jadwal['keterangan']);

        // --- ATURAN BATAS WAKTU ABSEN MASUK ---
        $waktuMasuk = Time::parse($tanggalSekarang . ' ' . $jadwal['jam_masuk'], 'Asia/Jakarta');
        $waktuPulang = Time::parse($tanggalSekarang . ' ' . $jadwal['jam_pulang'], 'Asia/Jakarta');

        $batasAwalMasuk = $waktuMasuk->subMinutes(30);
        $batasAkhirMasuk = $waktuPulang->subMinutes(30);

        if ($sekarang->isBefore($batasAwalMasuk)) {
            return $this->failForbidden('Belum waktunya presensi masuk. Absen dibuka pukul ' . $batasAwalMasuk->toTimeString());
        }

        if ($sekarang->isAfter($batasAkhirMasuk)) {
            return $this->failForbidden('Batas waktu presensi masuk hari ini telah habis.');
        }
        // --------------------------------------

        $absenHariIni = $this->absensiModel->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggalSekarang])->first();
        if ($absenHariIni) return $this->failResourceExists('Anda sudah presensi masuk hari ini.');

        $lat       = (float) $this->request->getPost('latitude');
        $lon       = (float) $this->request->getPost('longitude');
        $isFakeGps = (int) $this->request->getPost('is_fake_gps');
        $foto      = (string) $this->request->getPost('foto');

        $validasi = $this->absensiService->validasiGeofencing($lat, $lon, $isFakeGps);
        if ($validasi['status'] === 'Error') return $this->failServerError($validasi['message']);

        $status     = 'Hadir';
        $keterangan = 'Tepat Waktu';
        $menitTelat = 0;

        if (!$validasi['is_valid']) {
            $status     = 'Manipulasi';
            $keterangan = $validasi['message'];
        } elseif ($sekarang->isAfter($waktuMasuk)) {
            $status     = 'Terlambat';
            $menitTelat = abs($sekarang->difference($waktuMasuk)->getMinutes());
            $keterangan = "Terlambat {$menitTelat} Menit";
        }

        $fileName = $this->validateAndSaveBase64Image($foto, 'masuk', (string)$siswa['id_siswa']);
        if (!$fileName) return $this->failValidationErrors('Format file foto tidak valid atau terindikasi manipulasi.');

        try {
            $this->absensiModel->insert([
                'siswa_id'    => $siswa['id_siswa'],
                'tanggal'     => $tanggalSekarang,
                'jam_masuk'   => $sekarang->toTimeString(),
                'status'      => $status,
                'foto_masuk'  => $fileName,
                'lat_masuk'   => $lat,
                'long_masuk'  => $lon,
                'is_fake_gps' => $isFakeGps,
                'menit_telat' => $menitTelat,
                'keterangan'  => $keterangan
            ]);

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Presensi masuk berhasil tercatat.',
                'detail'  => $keterangan
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Gagal menyimpan data absensi.');
        }
    }

    public function pulang()
    {
        $siswa = $this->getSiswaAuth();

        $aturanValidasi = [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto'      => 'required'
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $sekarang        = Time::now('Asia/Jakarta');
        $tanggalSekarang = $sekarang->toDateString();
        $kodeHari        = $sekarang->getDayOfWeek();

        // Mengambil jadwal untuk memvalidasi jam pulang
        $jadwal = $this->getJadwalHariIni($tanggalSekarang, (string)$kodeHari);
        if ($jadwal['is_libur']) return $this->failForbidden('Hari ini libur: ' . $jadwal['keterangan']);

        $absen = $this->absensiModel->where(['siswa_id' => $siswa['id_siswa'], 'tanggal' => $tanggalSekarang])->first();

        // Validasi: Tidak absen masuk = tidak bisa absen pulang
        if (!$absen) return $this->failNotFound('Anda belum melakukan presensi masuk hari ini.');
        if ($absen['jam_pulang'] !== null) return $this->failResourceExists('Anda sudah presensi pulang hari ini.');

        // --- ATURAN BATAS WAKTU ABSEN PULANG ---
        $waktuPulang = Time::parse($tanggalSekarang . ' ' . $jadwal['jam_pulang'], 'Asia/Jakarta');
        $batasAkhirPulang = Time::parse($tanggalSekarang . ' 23:00:00', 'Asia/Jakarta');

        if ($sekarang->isBefore($waktuPulang)) {
            return $this->failForbidden('Belum waktunya presensi pulang. Jadwal pulang pukul ' . $waktuPulang->toTimeString());
        }

        if ($sekarang->isAfter($batasAkhirPulang)) {
            return $this->failForbidden('Batas waktu presensi pulang (23:00) telah habis.');
        }
        // ---------------------------------------

        $lat  = (float) $this->request->getPost('latitude');
        $lon  = (float) $this->request->getPost('longitude');
        $foto = (string) $this->request->getPost('foto');

        $fileName = $this->validateAndSaveBase64Image($foto, 'pulang', (string)$siswa['id_siswa']);
        if (!$fileName) return $this->failValidationErrors('Format file foto tidak valid.');

        try {
            $this->absensiModel->update($absen['id_absensi'], [
                'jam_pulang'  => $sekarang->toTimeString(),
                'foto_pulang' => $fileName,
                'lat_pulang'  => $lat,
                'long_pulang' => $lon
            ]);

            return $this->respondUpdated(['status' => 200, 'message' => 'Presensi pulang berhasil. Hati-hati di jalan!']);
        } catch (\Exception $e) {
            return $this->failServerError('Gagal menyimpan presensi pulang.');
        }
    }

    public function riwayat()
    {
        $siswa = $this->getSiswaAuth();

        $riwayat = $this->absensiModel
            ->where('siswa_id', $siswa['id_siswa'])
            ->orderBy('tanggal', 'DESC')
            ->findAll(30);

        return $this->respond(['status' => 200, 'data' => $riwayat]);
    }
}
