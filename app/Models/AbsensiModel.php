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
        'kelas_id',
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

    private function applyScopeKelas(AbsensiModel $model, ?int $kelasId = null): AbsensiModel
    {
        if ($kelasId !== null) {
            $model->where('absensi.kelas_id', $kelasId);
        }
        return $model;
    }

    public function getDashboardStats(string $tanggal, ?int $kelasId = null): array
    {
        $this->select('
            SUM(CASE WHEN absensi.status IN ("Hadir", "Terlambat", "Dispensasi") THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN absensi.status = "Alpa" THEN 1 ELSE 0 END) as alpa,
            SUM(CASE WHEN absensi.status = "Manipulasi" OR absensi.is_fake_gps = 1 THEN 1 ELSE 0 END) as fraud
        ')->where('absensi.tanggal', $tanggal);

        $result = $this->applyScopeKelas($this, $kelasId)->first();

        return [
            'hadir' => (int) ($result['hadir'] ?? 0),
            'alpa'  => (int) ($result['alpa'] ?? 0),
            'fraud' => (int) ($result['fraud'] ?? 0)
        ];
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

    public function getStatistikSiswa(string $idSiswa): array
    {
        $this->select('
            SUM(CASE WHEN status = "Hadir" THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = "Terlambat" THEN 1 ELSE 0 END) as terlambat,
            SUM(CASE WHEN status = "Sakit" THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN status = "Izin" THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN status = "Alpa" THEN 1 ELSE 0 END) as alpa
        ')->where('siswa_id', $idSiswa);

        $result = $this->first();

        $total = (int)($result['hadir'] ?? 0) + (int)($result['terlambat'] ?? 0) +
            (int)($result['sakit'] ?? 0) + (int)($result['izin'] ?? 0) +
            (int)($result['alpa'] ?? 0);

        return [
            'hadir'     => (int) ($result['hadir'] ?? 0),
            'terlambat' => (int) ($result['terlambat'] ?? 0),
            'sakit'     => (int) ($result['sakit'] ?? 0),
            'izin'      => (int) ($result['izin'] ?? 0),
            'alpa'      => (int) ($result['alpa'] ?? 0),
            'total'     => $total
        ];
    }

    public function getRiwayatAbsensiSiswa(string $idSiswa, ?string $startDate, ?string $endDate, int $perPage)
    {
        $builder = $this->where('siswa_id', $idSiswa);

        if (!empty($startDate)) {
            $builder->where('tanggal >=', $startDate);
        }

        if (!empty($endDate)) {
            $builder->where('tanggal <=', $endDate);
        }

        return $builder->orderBy('tanggal', 'DESC')->paginate($perPage, 'absensi');
    }

    /**
     * ✅ OPTIMASI BARU: Memisahkan logika query absensi harian dengan Pagination dan Search
     */
    public function getPaginatedAbsensiHarian(string $tanggal, ?int $kelasId = null, ?string $search = null, int $perPage = 20)
    {
        $builder = $this->select('absensi.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('absensi.tanggal', $tanggal);

        if (!empty($kelasId)) {
            $builder->where('siswa.kelas_id', $kelasId);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('siswa.nama_siswa', $search)
                ->orLike('siswa.nis', $search)
                ->groupEnd();
        }

        return $builder->orderBy('absensi.jam_masuk', 'DESC')->paginate($perPage, 'default');
    }
}
