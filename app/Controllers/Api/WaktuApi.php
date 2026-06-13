<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use App\Models\SiswaModel;

class WaktuApi extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $timezone = env('app.appTimezone', 'Asia/Jakarta');
        $waktuNow = Time::now($timezone);
        $kodeHari = (int) $waktuNow->format('N');
        $tanggalHariIni = $waktuNow->format('Y-m-d');

        $db = \Config\Database::connect();
        $siswaAuth = $this->request->siswaAuth ?? null;
        $aturanZona = null;

        if ($siswaAuth && isset($siswaAuth->id_siswa)) {
            $siswaModel = new SiswaModel();
            $aturanZona = $siswaModel->getAturanZonaSiswa((string)$siswaAuth->id_siswa, $kodeHari);
        }

        if (!$aturanZona) {
            $zonaDefault = $db->table('zona_absensi')->where('is_default', 1)->get()->getRowArray();
            $jadwalDefault = $db->table('zona_jadwal')->where(['zona_id' => $zonaDefault['id_zona'] ?? 1, 'kode_hari' => $kodeHari])->get()->getRowArray();

            $aturanZona = [
                'latitude'   => $zonaDefault['latitude'] ?? -6.200000,
                'longitude'  => $zonaDefault['longitude'] ?? 106.816666,
                'radius'     => $zonaDefault['radius'] ?? 50,
                'jam_masuk'  => $jadwalDefault['jam_masuk'] ?? '06:30:00',
                'jam_pulang' => $jadwalDefault['jam_pulang'] ?? '15:00:00',
                'is_libur'   => $jadwalDefault['is_libur'] ?? 0,
            ];
        }

        // 4. Kalkulasi Status Libur Berlapis (Prioritas Nasional -> Global -> Zona)
        $isLibur   = false;
        $namaLibur = '';

        // A. Cek Libur Nasional / Tanggal Merah
        $liburNasional = $db->table('hari_libur')->where('tanggal', $tanggalHariIni)->get()->getRowArray();
        if ($liburNasional) {
            $isLibur   = true;
            $namaLibur = $liburNasional['keterangan'];
        } else {
            // B. Cek Libur Akhir Pekan Global
            $liburGlobal = $db->table('jadwal_absen')->where('kode_hari', $kodeHari)->get()->getRowArray();
            if ($liburGlobal && $liburGlobal['is_libur'] == 1) {
                $isLibur   = true;
                $namaLibur = 'Libur Akhir Pekan (Global)';
            }
            // C. Cek Libur Khusus Zona (Misal shift PKL libur di hari kerja)
            elseif ($aturanZona['is_libur'] == 1) {
                $isLibur   = true;
                $namaLibur = 'Libur Jadwal ' . ($aturanZona['nama_zona'] ?? 'Zona PKL');
            }
        }

        // 5. Kirim ke Frontend Aplikasi Flutter dengan format yang persis sama (Backward Compatibility)
        return $this->respond([
            'status'  => 200,
            'message' => 'Berhasil mengambil data konfigurasi server & lokasi zona',
            'data'    => [
                'waktu'       => $waktuNow->toDateTimeString(),
                'jam_masuk'   => $aturanZona['jam_masuk'],
                'jam_pulang'  => $aturanZona['jam_pulang'],
                'lat_sekolah' => (float) $aturanZona['latitude'],
                'lon_sekolah' => (float) $aturanZona['longitude'],
                'radius'      => (float) $aturanZona['radius'],
                'is_libur'    => $isLibur,
                'nama_libur'  => $namaLibur
            ]
        ]);
    }
}
