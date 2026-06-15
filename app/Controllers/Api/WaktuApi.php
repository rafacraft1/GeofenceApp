<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use App\Models\ZonaModel;
use App\Models\HariLiburModel;
use App\Libraries\JWTAuth;

class WaktuApi extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $zonaModel  = new ZonaModel();
        $liburModel = new HariLiburModel();
        $db         = \Config\Database::connect();

        $zona = null;

        // 1. Ekstrak Token JWT (Sama persis dengan metode di ApiAuthFilter)
        $header = (string) $this->request->getHeaderLine('Authorization');
        $token  = str_replace('Bearer ', '', $header);

        if (!empty($token)) {
            try {
                $jwtAuth = new JWTAuth();
                $decoded = $jwtAuth->decodeToken($token);

                if ($decoded['status'] === 'valid') {
                    $idSiswa = $decoded['data']->id_siswa ?? null;

                    if ($idSiswa) {
                        // Ambil data siswa & zona secara REAL-TIME (Tanpa Cache)
                        $siswa = $db->table('siswa')
                            ->select('siswa.zona_id as zona_siswa, kelas.zona_id as zona_kelas')
                            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
                            ->where('siswa.id_siswa', $idSiswa)
                            ->get()
                            ->getRowArray();

                        if ($siswa) {
                            // Prioritas: 1. Zona Individu Siswa (PKL), 2. Zona Kelas, 3. Null
                            $targetZonaId = $siswa['zona_siswa'] ?? $siswa['zona_kelas'] ?? null;

                            if ($targetZonaId) {
                                $zona = $zonaModel->find($targetZonaId);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Abaikan jika token rusak/expired, biarkan API jatuh ke Zona Default
            }
        }

        // 2. Jika tidak ada zona PKL / Belum Login -> Ambil Zona Default (Sekolah)
        if (!$zona) {
            // REAL-TIME (Tanpa Cache)
            $zona = $zonaModel->where('is_default', 1)->first();
        }

        // Safety check jika tabel zona kosong
        if (!$zona) {
            return $this->failServerError('Data Zona Absensi belum dikonfigurasi di database server.');
        }

        // 3. Cek Hari Libur Nasional / Custom secara REAL-TIME
        $isLibur = false;
        $namaLibur = '';
        $tz = getenv('app.appTimezone') ?: 'Asia/Jakarta';
        $tanggalSekarang = Time::now($tz)->format('Y-m-d');

        $cekLibur = $liburModel->where('tanggal', $tanggalSekarang)->first();
        if ($cekLibur) {
            $isLibur = true;
            $namaLibur = $cekLibur['keterangan'];
        }

        // 4. Ambil Jadwal Absensi Khusus untuk Zona Terpilih (PKL/Sekolah) secara REAL-TIME
        $kodeHariIni = Time::now($tz)->format('N'); // 1 = Senin, 7 = Minggu

        $jadwal = $db->table('zona_jadwal')
            ->where('zona_id', $zona['id_zona'])
            ->where('kode_hari', $kodeHariIni)
            ->get()
            ->getRowArray();

        // Cek jika jadwal diset libur akhir pekan
        if ($jadwal && $jadwal['is_libur'] == 1) {
            $isLibur = true;
            $namaLibur = $namaLibur ?: 'Libur Akhir Pekan';
        }

        // 5. Kembalikan Response
        return $this->respond([
            'status'      => 200,
            'waktu'       => Time::now($tz)->toDateTimeString(),
            'is_libur'    => $isLibur,
            'nama_libur'  => $namaLibur,
            'lat_sekolah' => (float) $zona['latitude'],
            'lon_sekolah' => (float) $zona['longitude'],
            'radius'      => (float) $zona['radius'],
            'nama_zona'   => $zona['nama_zona'],
            'jam_masuk'   => $jadwal['jam_masuk'] ?? '07:00:00',
            'jam_pulang'  => $jadwal['jam_pulang'] ?? '15:00:00',
            'pengaturan'  => [
                'jam_buka' => $jadwal['waktu_buka_absen'] ?? '06:00:00'
            ]
        ]);
    }
}
