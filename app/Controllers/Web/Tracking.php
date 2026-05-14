<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\PengaturanModel;
use App\Models\AbsensiModel;

class Tracking extends BaseController
{
    protected SiswaModel $siswaModel;
    protected KelasModel $kelasModel;
    protected PengaturanModel $pengaturanModel;
    protected AbsensiModel $absensiModel;

    public function __construct()
    {
        $this->siswaModel      = new SiswaModel();
        $this->kelasModel      = new KelasModel();
        $this->pengaturanModel = new PengaturanModel();
        $this->absensiModel    = new AbsensiModel();
    }

    public function index(string|null $targetId = null)
    {
        $kelasFilter = $this->request->getGet('kelas_id');

        $config    = $this->pengaturanModel->find(1);
        $listKelas = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

        $this->siswaModel->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelasFilter)) {
            $this->siswaModel->where('siswa.kelas_id', $kelasFilter);
        }

        $listSiswa = $this->siswaModel->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->findAll();

        $data = [
            'title'       => 'Radar Live Tracking',
            'config'      => $config,
            'list_siswa'  => $listSiswa,
            'list_kelas'  => $listKelas,
            'kelas_aktif' => $kelasFilter,
            'target_id'   => $targetId
        ];

        return view('web/tracking', $data);
    }

    /**
     * Endpoint API Internal untuk AJAX Frontend
     */
    public function getLocation(string $idSiswa)
    {
        $siswa = $this->siswaModel->find($idSiswa);

        // PERBAIKAN: Baca dari kolom pergerakan live, BUKAN dari tabel absensi
        if ($siswa && !empty($siswa['lat_terakhir']) && !empty($siswa['long_terakhir'])) {
            return $this->response->setJSON([
                'status'      => 200,
                'lat'         => (float) $siswa['lat_terakhir'],
                'lng'         => (float) $siswa['long_terakhir'],
                'nama'        => $siswa['nama_siswa'],
                'last_update' => $siswa['updated_at'] // Waktu live terakhir
            ]);
        }

        return $this->response->setJSON(['status' => 404, 'message' => 'Belum ada sinyal radar dari perangkat siswa.']);
    }

    /**
     * Endpoint API Internal untuk Ping Paksa (Firebase Cloud Messaging)
     */
    public function pingSiswa(string $idSiswa)
    {
        helper('fcm');
        $siswa = $this->siswaModel->find($idSiswa);

        if ($siswa && !empty($siswa['fcm_token'])) {
            $result = send_fcm_notification(
                (string) $siswa['fcm_token'],
                "PING_LOCATION",
                "Admin meminta pembaruan lokasi radar.",
                ['action' => 'fetch_location']
            );

            if ($result) {
                return $this->response->setJSON(['status' => 200, 'message' => 'Sinyal PING berhasil dikirim ke perangkat.']);
            }
        }

        return $this->response->setJSON(['status' => 400, 'message' => 'Gagal PING. Perangkat offline atau tidak terikat.']);
    }
}
