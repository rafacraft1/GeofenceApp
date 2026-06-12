<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\ZonaModel;

class Zona extends BaseController
{
    protected ZonaModel $zonaModel;

    public function __construct()
    {
        $this->zonaModel = new ZonaModel();
    }

    public function index()
    {
        $db = \Config\Database::connect();

        $zonas = $this->zonaModel->orderBy('is_default', 'DESC')->orderBy('nama_zona', 'ASC')->findAll();

        // Memuat Jadwal untuk masing-masing Zona agar bisa diedit di UI
        foreach ($zonas as &$z) {
            $z['jadwal'] = $db->table('zona_jadwal')->where('zona_id', $z['id_zona'])->orderBy('kode_hari', 'ASC')->get()->getResultArray();
        }

        $data = [
            'title'     => 'Manajemen Zona & Jadwal PKL',
            'zonas'     => $zonas,
            'all_kelas' => $db->table('kelas')
                ->select('kelas.*, z.nama_zona as nama_zona_kelas')
                ->join('zona_absensi z', 'z.id_zona = kelas.zona_id', 'left')
                ->orderBy('kelas.nama_kelas', 'ASC')
                ->get()->getResultArray(),
            'all_siswa' => $db->table('siswa')
                ->select('siswa.id_siswa, siswa.nis, siswa.nama_siswa, siswa.zona_id, kelas.nama_kelas, z.nama_zona as nama_zona_siswa')
                ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
                ->join('zona_absensi z', 'z.id_zona = siswa.zona_id', 'left')
                ->orderBy('kelas.nama_kelas', 'ASC')
                ->orderBy('siswa.nama_siswa', 'ASC')
                ->get()->getResultArray()
        ];

        return view('web/zona', $data);
    }

    public function store()
    {
        $this->zonaModel->insert([
            'nama_zona' => $this->request->getPost('nama_zona'),
            'latitude'  => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'radius'    => $this->request->getPost('radius'),
        ]);

        $zonaId = $this->zonaModel->getInsertID();

        // Auto-generate 7 Hari Jadwal Default untuk Zona Baru ini
        $db = \Config\Database::connect();
        $jadwalDefault = [];
        $hari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        foreach ($hari as $kode => $nama) {
            $jadwalDefault[] = [
                'zona_id'          => $zonaId,
                'kode_hari'        => $kode,
                'nama_hari'        => $nama,
                'waktu_buka_absen' => $this->request->getPost('waktu_buka_absen'),
                'jam_masuk'        => $this->request->getPost('jam_masuk'),
                'jam_pulang'       => ($kode == 5) ? '11:30:00' : $this->request->getPost('jam_pulang'), // Jumat default pulang lebih awal
                'is_libur'         => ($kode >= 6) ? 1 : 0
            ];
        }
        $db->table('zona_jadwal')->insertBatch($jadwalDefault);

        return redirect()->to('/admin/zona')->with('success', 'Zona absensi baru beserta jadwal default berhasil dibuat.');
    }

    public function update(string $id)
    {
        $this->zonaModel->update($id, [
            'nama_zona' => $this->request->getPost('nama_zona'),
            'latitude'  => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'radius'    => $this->request->getPost('radius'),
        ]);

        return redirect()->to('/admin/zona')->with('success', 'Lokasi dan radius zona absensi berhasil diperbarui.');
    }

    public function updateJadwal(string $idZona)
    {
        $db = \Config\Database::connect();

        $buka   = $this->request->getPost('buka');
        $masuk  = $this->request->getPost('masuk');
        $pulang = $this->request->getPost('pulang');
        $libur  = $this->request->getPost('is_libur') ?? [];

        $db->transStart();

        for ($kodeHari = 1; $kodeHari <= 7; $kodeHari++) {
            $isLibur = in_array($kodeHari, $libur) ? 1 : 0;
            $db->table('zona_jadwal')->where(['zona_id' => $idZona, 'kode_hari' => $kodeHari])->update([
                'waktu_buka_absen' => $buka[$kodeHari],
                'jam_masuk'        => $masuk[$kodeHari],
                'jam_pulang'       => $pulang[$kodeHari],
                'is_libur'         => $isLibur
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/zona')->with('error', 'Gagal menyimpan pembaruan jadwal.');
        }

        return redirect()->to('/admin/zona')->with('success', 'Jadwal & Jam Absensi untuk zona ini berhasil diperbarui.');
    }

    public function assignAnggota(string $id)
    {
        $kelasIds = $this->request->getPost('kelas_ids') ?? [];
        $siswaIds = $this->request->getPost('siswa_ids') ?? [];

        $db = \Config\Database::connect();
        $db->transStart();

        $db->table('kelas')->where('zona_id', $id)->update(['zona_id' => null]);
        $db->table('siswa')->where('zona_id', $id)->update(['zona_id' => null]);

        if (!empty($kelasIds)) {
            $db->table('kelas')->whereIn('id_kelas', $kelasIds)->update(['zona_id' => $id]);
        }

        if (!empty($siswaIds)) {
            $db->table('siswa')->whereIn('id_siswa', $siswaIds)->update(['zona_id' => $id]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/zona')->with('error', 'Terjadi kesalahan sistem saat mengatur anggota zona.');
        }

        return redirect()->to('/admin/zona')->with('success', 'Anggota (Kelas & Siswa) zona PKL/Kegiatan berhasil diperbarui.');
    }

    public function delete(string $id)
    {
        $zona = $this->zonaModel->find($id);
        if (!$zona) return redirect()->to('/admin/zona')->with('error', 'Zona tidak ditemukan.');
        if ($zona['is_default'] == 1) return redirect()->to('/admin/zona')->with('error', 'Akses Ditolak: Zona Default tidak boleh dihapus.');

        $this->zonaModel->delete($id);
        return redirect()->to('/admin/zona')->with('success', 'Data zona absensi berhasil dihapus.');
    }

    public function setDefault(string $id)
    {
        $this->zonaModel->set('is_default', 0)->update();
        $this->zonaModel->update($id, ['is_default' => 1]);
        return redirect()->to('/admin/zona')->with('success', 'Status Zona Default berhasil dipindahkan.');
    }
}
