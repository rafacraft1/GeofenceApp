<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use App\Models\ZonaModel;
use App\Models\SiswaModel;
use App\Models\HariLiburModel;
use App\Libraries\JWTAuth;

class WaktuApi extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $zonaModel  = new ZonaModel();
        $siswaModel = new SiswaModel();
        $liburModel = new HariLiburModel();
        $db         = \Config\Database::connect();

        $zona = null;

        // 1. Ekstrak Token JWT secara manual untuk mendeteksi siapa yang login
        $header = $this->request->getHeaderLine('Authorization');
        if (!empty($header)) {
            $token = explode(' ', $header)[1] ?? null;
            if ($token) {
                try {
                    $jwt = new JWTAuth();
                    $decodedResult = $jwt->decodeToken($token);

                    // PERBAIKAN: Baca id_siswa dari dalam array ['data'] sesuai struktur JWTAuth.php
                    if (isset($decodedResult['status']) && $decodedResult['status'] === 'valid' && isset($decodedResult['data'])) {
                        // $decodedResult['data'] adalah stdClass (object) berdasarkan bawaan library Firebase/JWT
                        $idSiswa = $decodedResult['data']->id_siswa ?? null;

                        if ($idSiswa) {
                            // Gunakan cache untuk optimasi performa karena endpoint ini di-hit setiap saat
                            $siswa = cache()->remember("siswa_zona_{$idSiswa}", 300, function () use ($siswaModel, $idSiswa) {
                                return $siswaModel->select('zona_id')->find($idSiswa);
                            });

                            // Jika siswa dipasangkan ke zona PKL tertentu, ambil zona tersebut
                            if ($siswa && !empty($siswa['zona_id'])) {
                                $zona = $zonaModel->find($siswa['zona_id']);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Token invalid/expired, biarkan sistem melakukan fallback ke zona default
                }
            }
        }

        // 2. Jika bukan siswa PKL (atau belum login), ambil Zona Default (Sekolah)
        if (!$zona) {
            $zona = cache()->remember('zona_default', 86400, function () use ($zonaModel) {
                return $zonaModel->where('is_default', 1)->first();
            });
        }

        // Safety check jika database zona masih kosong
        if (!$zona) {
            return $this->failServerError('Data Zona Absensi belum dikonfigurasi di database server.');
        }

        // 3. Cek Hari Libur Nasional / Custom
        $isLibur = false;
        $namaLibur = '';
        $tz = getenv('app.appTimezone') ?: 'Asia/Jakarta';
        $tanggalSekarang = Time::now($tz)->format('Y-m-d');

        $cekLibur = cache()->remember("libur_{$tanggalSekarang}", 86400, function () use ($liburModel, $tanggalSekarang) {
            return $liburModel->where('tanggal', $tanggalSekarang)->first();
        });

        if ($cekLibur) {
            $isLibur = true;
            $namaLibur = $cekLibur['keterangan'];
        }

        // 4. Ambil Jadwal Absensi Khusus untuk Zona yang Terpilih Hari Ini
        $kodeHariIni = Time::now($tz)->format('N'); // 1 = Senin, 7 = Minggu
        $jadwal = cache()->remember("jadwal_zona_{$zona['id_zona']}_{$kodeHariIni}", 86400, function () use ($db, $zona, $kodeHariIni) {
            return $db->table('zona_jadwal')
                ->where('zona_id', $zona['id_zona'])
                ->where('kode_hari', $kodeHariIni)
                ->get()
                ->getRowArray();
        });

        // Cek Libur Akhir Pekan dari Jadwal Zona
        if ($jadwal && $jadwal['is_libur'] == 1) {
            $isLibur = true;
            $namaLibur = $namaLibur ?: 'Libur Akhir Pekan';
        }

        // 5. Kembalikan Response Dinamis
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
