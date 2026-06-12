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
        if ($kelasId !== null) $model->where('absensi.kelas_id', $kelasId);
        return $model;
    }

    public function getDashboardStats(string $tanggal, ?int $kelasId = null): array
    {
        $builder = $this->select('
            SUM(CASE WHEN status IN ("Hadir", "Terlambat", "Dispensasi") THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = "Alpa" THEN 1 ELSE 0 END) as alpa,
            SUM(CASE WHEN status = "Manipulasi" THEN 1 ELSE 0 END) as fraud
        ')->where('tanggal', $tanggal);

        $this->applyScopeKelas($builder, $kelasId);
        return $builder->first() ?? ['hadir' => 0, 'alpa' => 0, 'fraud' => 0];
    }

    public function getDashboardDistribution(string $tanggal, ?int $kelasId = null): array
    {
        $builder = $this->select('
            SUM(CASE WHEN status = "Hadir" THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = "Dispensasi" THEN 1 ELSE 0 END) as dispensasi,
            SUM(CASE WHEN status = "Terlambat" THEN 1 ELSE 0 END) as terlambat,
            SUM(CASE WHEN status = "Sakit" THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN status = "Izin" THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN status = "Alpa" THEN 1 ELSE 0 END) as alpa
        ')->where('tanggal', $tanggal);

        $this->applyScopeKelas($builder, $kelasId);
        $res = $builder->first() ?? ['hadir' => 0, 'dispensasi' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];

        return [(int)$res['hadir'], (int)$res['dispensasi'], (int)$res['terlambat'], (int)$res['sakit'], (int)$res['izin'], (int)$res['alpa']];
    }

    public function getFraudList(string $tanggal, ?int $kelasId = null): array
    {
        $builder = $this->select('absensi.id_absensi, absensi.jam_masuk, absensi.is_fake_gps, absensi.lat_masuk, absensi.long_masuk, siswa.nama_siswa, kelas.nama_kelas as kelas')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = absensi.kelas_id', 'left')
            ->where('absensi.tanggal', $tanggal)
            ->groupStart()->where('absensi.status', 'Manipulasi')->orWhere('absensi.is_fake_gps', 1)->groupEnd();

        $this->applyScopeKelas($builder, $kelasId);
        return $builder->orderBy('absensi.jam_masuk', 'DESC')->findAll();
    }

    public function getLeaderboardKelas(string $tanggal, ?int $kelasId = null): array
    {
        $builder = $this->select('kelas.nama_kelas, COUNT(absensi.id_absensi) as total_hadir')
            ->join('kelas', 'kelas.id_kelas = absensi.kelas_id')
            ->where('absensi.tanggal', $tanggal)
            ->whereIn('absensi.status', ['Hadir', 'Dispensasi', 'Terlambat'])
            ->groupBy('absensi.kelas_id')
            ->orderBy('total_hadir', 'DESC')->limit(5);

        $this->applyScopeKelas($builder, $kelasId);
        return $builder->findAll();
    }

    public function getTrendKehadiran(string $startDate, string $endDate, ?int $kelasId = null): array
    {
        $builder = $this->select('tanggal, status, COUNT(id_absensi) as total')
            ->where('tanggal >=', $startDate)->where('tanggal <=', $endDate)->groupBy('tanggal, status');

        $this->applyScopeKelas($builder, $kelasId);
        return $builder->findAll();
    }

    public function getPaginatedAbsensiHarian(string $tanggal, ?int $kelasId = null, ?string $search = null, int $perPage = 20, string $sortCol = 'jam_masuk', string $sortDir = 'desc')
    {
        $builder = $this->select('absensi.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = absensi.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('absensi.tanggal', $tanggal);

        if (!empty($kelasId)) $builder->where('siswa.kelas_id', $kelasId);
        if (!empty($search)) $builder->groupStart()->like('siswa.nama_siswa', $search)->orLike('siswa.nis', $search)->groupEnd();

        $orderCol = (['nama_siswa' => 'siswa.nama_siswa', 'jam_masuk' => 'absensi.jam_masuk', 'status' => 'absensi.status'][$sortCol]) ?? 'absensi.jam_masuk';
        return $builder->orderBy($orderCol, $sortDir)->paginate($perPage, 'default');
    }

    // WAJIB KEMBALIKAN FUNGSI INI UNTUK PROFIL 360 (DETAIL SISWA)
    public function getRiwayatAbsensiSiswa(string $idSiswa, ?string $startDate = null, ?string $endDate = null, int $perPage = 10)
    {
        $builder = $this->where('siswa_id', $idSiswa);
        if (!empty($startDate)) $builder->where('tanggal >=', $startDate);
        if (!empty($endDate)) $builder->where('tanggal <=', $endDate);
        return $builder->orderBy('tanggal', 'DESC')->paginate($perPage, 'absensi');
    }

    public function getStatistikSiswa(string $idSiswa): array
    {
        $result = $this->select('
            SUM(CASE WHEN status = "Hadir" THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = "Terlambat" THEN 1 ELSE 0 END) as terlambat,
            SUM(CASE WHEN status = "Sakit" THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN status = "Izin" THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN status = "Alpa" THEN 1 ELSE 0 END) as alpa,
            SUM(CASE WHEN status = "Dispensasi" THEN 1 ELSE 0 END) as dispensasi,
            SUM(CASE WHEN status = "Manipulasi" THEN 1 ELSE 0 END) as manipulasi
        ')->where('siswa_id', $idSiswa)->first();

        return $result ?? ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0, 'dispensasi' => 0, 'manipulasi' => 0];
    }
}
