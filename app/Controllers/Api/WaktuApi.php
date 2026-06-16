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

        $header = (string) $this->request->getHeaderLine('Authorization');
        $token  = str_replace('Bearer ', '', $header);

        if (!empty($token)) {
            try {
                $jwtAuth = new JWTAuth();
                $decodedResult = $jwtAuth->decodeToken($token);

                if (isset($decodedResult['status']) && $decodedResult['status'] === 'valid' && isset($decodedResult['data'])) {
                    // Support array dan object secara universal untuk cegah fatal error
                    $dataJwt = $decodedResult['data'];
                    $idSiswa = is_array($dataJwt) ? ($dataJwt['id_siswa'] ?? null) : ($dataJwt->id_siswa ?? null);

                    if ($idSiswa) {
                        $siswa = $db->table('siswa')
                            ->select('siswa.zona_id as zona_siswa, kelas.zona_id as zona_kelas')
                            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
                            ->where('siswa.id_siswa', $idSiswa)
                            ->get()
                            ->getRowArray();

                        if ($siswa) {
                            $targetZonaId = $siswa['zona_siswa'] ?? $siswa['zona_kelas'] ?? null;
                            if ($targetZonaId) {
                                $zona = $zonaModel->find($targetZonaId);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Abaikan jika token invalid
            }
        }

        if (!$zona) {
            $zona = $zonaModel->where('is_default', 1)->first();
        }

        if (!$zona) {
            return $this->failServerError('Data Zona Absensi belum dikonfigurasi di database.');
        }

        $tz = getenv('app.appTimezone') ?: 'Asia/Jakarta';
        $tanggalSekarang = Time::now($tz)->format('Y-m-d');

        $isLibur = false;
        $namaLibur = '';

        $cekLibur = $liburModel->where('tanggal', $tanggalSekarang)->first();
        if ($cekLibur) {
            $isLibur = true;
            $namaLibur = $cekLibur['keterangan'];
        }

        $kodeHariIni = Time::now($tz)->format('N');

        $jadwal = $db->table('zona_jadwal')
            ->where('zona_id', $zona['id_zona'])
            ->where('kode_hari', $kodeHariIni)
            ->get()
            ->getRowArray();

        if ($jadwal && $jadwal['is_libur'] == 1) {
            $isLibur = true;
            $namaLibur = $namaLibur ?: 'Libur Akhir Pekan';
        }

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
