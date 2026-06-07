<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiModel extends Model
{
    protected $table            = 'absensi';
    protected $primaryKey       = 'id_absensi';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'siswa_id',
        'kelas_id', // Historical snapshot
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
        'foto_masuk',
        'foto_pulang',
        'lat_masuk',
        'long_masuk',
        'lat_pulang',
        'long_pulang',
        'is_fake_gps',
        'menit_telat',
        'keterangan'
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // ========================================================================
    // LOGIKA ANALYTICS (Best Practice: Fat Model)
    // ========================================================================

    /**
     * Helper internal untuk memfilter query berdasarkan ID Kelas jika role-nya wali kelas.
     * Mengembalikan instance AbsensiModel (bukan BaseBuilder) agar bisa dirantai (chaining).
     * * @param AbsensiModel $model
     * @param int|null $kelasId
     * @return AbsensiModel
     */
    private function applyScopeKelas(AbsensiModel $model, ?int $kelasId = null): AbsensiModel
    {
        if ($kelasId !== null) {
            // Menggunakan prefix 'absensi.' agar aman saat ada JOIN
            $model->where('absensi.kelas_id', $kelasId);
        }
        return $model;
    }

    public function getDashboardStats(string $tanggal, ?int $kelasId = null): array
    {
        // Dalam CI4, countAllResults() otomatis mereset builder, 
        // jadi kita bisa memanggilnya berurutan tanpa saling menimpa query.

        $hadir = $this->applyScopeKelas($this, $kelasId)
            ->where('absensi.tanggal', $tanggal)
            ->whereIn('absensi.status', ['Hadir', 'Terlambat', 'Dispensasi'])
            ->countAllResults();

        $alpa  = $this->applyScopeKelas($this, $kelasId)
            ->where('absensi.tanggal', $tanggal)
            ->where('absensi.status', 'Alpa')
            ->countAllResults();

        $fraud = $this->applyScopeKelas($this, $kelasId)
            ->where('absensi.tanggal', $tanggal)
            ->groupStart()
            ->where('absensi.status', 'Manipulasi')
            ->orWhere('absensi.is_fake_gps', 1)
            ->groupEnd()
            ->countAllResults();

        return ['hadir' => $hadir, 'alpa' => $alpa, 'fraud' => $fraud];
    }

    public function getDashboardDistribution(string $tanggal, ?int $kelasId = null): array
    {
        $result = $this->applyScopeKelas($this, $kelasId)
            ->select('status, COUNT(id_absensi) as total')
            ->where('absensi.tanggal', $tanggal)
            ->groupBy('status')
            ->findAll();

        $map = ['Hadir' => 0, 'Dispensasi' => 0, 'Terlambat' => 0, 'Sakit' => 0, 'Izin' => 0, 'Alpa' => 0];
        foreach ($result as $row) {
            if (isset($map[$row['status']])) {
                $map[$row['status']] = (int) $row['total'];
            }
        }
        return array_values($map);
    }

    public function getLeaderboardKelas(string $tanggal, ?int $kelasId = null): array
    {
        $this->applyScopeKelas($this, $kelasId)
            ->select('kelas.nama_kelas, COUNT(absensi.id_absensi) as total_hadir')
            ->join('kelas', 'kelas.id_kelas = absensi.kelas_id')
            ->where('absensi.tanggal', $tanggal)
            ->whereIn('absensi.status', ['Hadir', 'Terlambat', 'Dispensasi'])
            ->groupBy('absensi.kelas_id')
            ->orderBy('total_hadir', 'DESC')
            ->limit(5);

        return $this->findAll();
    }

    public function getTrendKehadiran(string $startDate, string $endDate, ?int $kelasId = null): array
    {
        $this->applyScopeKelas($this, $kelasId)
            ->select('tanggal, status, COUNT(id_absensi) as total')
            ->where('tanggal >=', $startDate)
            ->where('tanggal <=', $endDate)
            ->whereIn('status', ['Hadir', 'Dispensasi', 'Terlambat', 'Alpa'])
            ->groupBy('tanggal, status');

        return $this->findAll();
    }

    public function getFraudList(string $tanggal, ?int $kelasId = null): array
    {
        $this->applyScopeKelas($this, $kelasId)
            ->select('absensi.jam_masuk, absensi.status, absensi.is_fake_gps, absensi.lat_masuk, absensi.long_masuk, siswa.nama_siswa, kelas.nama_kelas as kelas, siswa.nis, siswa.foto_profil')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = absensi.kelas_id', 'left')
            ->where('absensi.tanggal', $tanggal)
            ->groupStart()
            ->where('absensi.status', 'Manipulasi')
            ->orWhere('absensi.is_fake_gps', 1)
            ->groupEnd()
            ->orderBy('absensi.jam_masuk', 'DESC');

        return $this->findAll();
    }
}
