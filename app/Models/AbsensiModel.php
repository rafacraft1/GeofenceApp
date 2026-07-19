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

    protected array $tempOldData = [];
    protected $afterInsert       = ['auditInsert'];
    protected $beforeUpdate      = ['auditBeforeUpdate'];
    protected $afterUpdate       = ['auditAfterUpdate'];
    protected $beforeDelete      = ['auditBeforeDelete'];
    protected $afterDelete       = ['auditAfterDelete'];

    protected function auditInsert(array $data): array
    {
        if (!isset($data['result']) || !$data['result']) return $data;
        $auditService = new \App\Services\AuditService();
        $auditService->log('INSERT', $this->table, null, (array) ($data['data'] ?? []));
        return $data;
    }

    protected function auditBeforeUpdate(array $data): array
    {
        $ids = $data['id'] ?? [];
        if (!is_array($ids)) $ids = [$ids];

        if (!empty($ids)) {
            $this->tempOldData = $this->db->table($this->table)->whereIn($this->primaryKey, $ids)->get()->getResultArray();
        } else {
            $this->tempOldData = [];
        }
        return $data;
    }

    protected function auditAfterUpdate(array $data): array
    {
        if (!isset($data['result']) || !$data['result']) return $data;
        $auditService = new \App\Services\AuditService();

        foreach ($this->tempOldData as $oldRow) {
            $auditService->log('UPDATE', $this->table, $oldRow, (array) ($data['data'] ?? []));
        }
        $this->tempOldData = [];
        return $data;
    }

    protected function auditBeforeDelete(array $data): array
    {
        $ids = $data['id'] ?? [];
        if (!is_array($ids)) $ids = [$ids];

        if (!empty($ids)) {
            $this->tempOldData = $this->db->table($this->table)->whereIn($this->primaryKey, $ids)->get()->getResultArray();
        } else {
            $this->tempOldData = [];
        }
        return $data;
    }

    protected function auditAfterDelete(array $data): array
    {
        if (!isset($data['result']) || !$data['result']) return $data;
        $auditService = new \App\Services\AuditService();

        foreach ($this->tempOldData as $oldRow) {
            $auditService->log('DELETE', $this->table, $oldRow, null);
        }
        $this->tempOldData = [];
        return $data;
    }

    private function applyScopeKelas(AbsensiModel $model, ?int $kelasId = null): AbsensiModel
    {
        if ($kelasId !== null) $model->where('absensi.kelas_id', $kelasId);
        return $model;
    }

    // -------------------------------------------------------------------
    // FUNGSI DASHBOARD & STATISTIK (DIPERBAIKI)
    // -------------------------------------------------------------------

    public function getDashboardStats(string $startDate, ?int $kelasId = null, ?string $endDate = null): array
    {
        $endDate = $endDate ?? $startDate;

        $builder = $this->select('
            SUM(CASE WHEN status IN ("Hadir", "Terlambat", "Dispensasi") THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = "Alpa" THEN 1 ELSE 0 END) as alpa
        ')->where('tanggal >=', $startDate)->where('tanggal <=', $endDate);

        $this->applyScopeKelas($builder, $kelasId);
        $stats = $builder->first() ?? ['hadir' => 0, 'alpa' => 0];

        // Hitung total fraud (keamanan berlapis) dari tabel log_fraud
        $fraudBuilder = $this->db->table('log_fraud');
        $fraudBuilder->where('DATE(created_at) >=', $startDate)->where('DATE(created_at) <=', $endDate);
        if ($kelasId) {
            $fraudBuilder->join('siswa', 'siswa.id_siswa = log_fraud.siswa_id');
            $fraudBuilder->where('siswa.kelas_id', $kelasId);
        }
        $fraudCount = $fraudBuilder->countAllResults();

        return [
            'hadir' => (int) ($stats['hadir'] ?? 0),
            'alpa'  => (int) ($stats['alpa'] ?? 0),
            'fraud' => $fraudCount
        ];
    }

    public function getDashboardDistribution(string $startDate, ?int $kelasId = null, ?string $endDate = null): array
    {
        $endDate = $endDate ?? $startDate;

        $builder = $this->select('
            SUM(CASE WHEN status = "Hadir" THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = "Dispensasi" THEN 1 ELSE 0 END) as dispensasi,
            SUM(CASE WHEN status = "Terlambat" THEN 1 ELSE 0 END) as terlambat,
            SUM(CASE WHEN status = "Sakit" THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN status = "Izin" THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN status = "Alpa" THEN 1 ELSE 0 END) as alpa
        ')->where('tanggal >=', $startDate)->where('tanggal <=', $endDate);

        $this->applyScopeKelas($builder, $kelasId);
        $res = $builder->first() ?? ['hadir' => 0, 'dispensasi' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];

        return [(int)$res['hadir'], (int)$res['dispensasi'], (int)$res['terlambat'], (int)$res['sakit'], (int)$res['izin'], (int)$res['alpa']];
    }

    /**
     * Menghitung ringkasan jumlah per status kehadiran untuk satu tanggal tertentu.
     * Digunakan di halaman Absensi Harian untuk menampilkan stat cards.
     */
    public function getDailySummary(string $tanggal, ?int $kelasId = null): array
    {
        $builder = $this->select('
            SUM(CASE WHEN status = "Hadir" THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = "Terlambat" THEN 1 ELSE 0 END) as terlambat,
            SUM(CASE WHEN status = "Dispensasi" THEN 1 ELSE 0 END) as dispensasi,
            SUM(CASE WHEN status = "Sakit" THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN status = "Izin" THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN status = "Alpa" THEN 1 ELSE 0 END) as alpa
        ')->where('tanggal', $tanggal);

        $this->applyScopeKelas($builder, $kelasId);
        $res = $builder->first() ?? [];

        return [
            'hadir'      => (int)($res['hadir'] ?? 0),
            'terlambat'  => (int)($res['terlambat'] ?? 0),
            'dispensasi' => (int)($res['dispensasi'] ?? 0),
            'sakit'      => (int)($res['sakit'] ?? 0),
            'izin'       => (int)($res['izin'] ?? 0),
            'alpa'       => (int)($res['alpa'] ?? 0),
        ];
    }

    public function getFraudList(string $startDate, ?int $kelasId = null, ?string $endDate = null): array
    {
        $endDate = $endDate ?? $startDate;

        $builder = $this->db->table('log_fraud');

        $builder->select('
            log_fraud.id_log, 
            log_fraud.created_at as jam_masuk, 
            log_fraud.lat_fraud as lat_masuk, 
            log_fraud.long_fraud as long_masuk, 
            (CASE WHEN log_fraud.tipe_fraud LIKE \'%Fake GPS%\' THEN 1 ELSE 0 END) as is_fake_gps, 
            siswa.nama_siswa, 
            kelas.nama_kelas as kelas
        ');
        $builder->join('siswa', 'siswa.id_siswa = log_fraud.siswa_id');
        $builder->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        $builder->where('DATE(log_fraud.created_at) >=', $startDate);
        $builder->where('DATE(log_fraud.created_at) <=', $endDate);
        $builder->where('log_fraud.lat_fraud IS NOT NULL');

        if ($kelasId) {
            $builder->where('siswa.kelas_id', $kelasId);
        }

        $builder->orderBy('log_fraud.created_at', 'DESC');
        return $builder->get()->getResultArray();
    }

    // -------------------------------------------------------------------
    // FUNGSI UTAMA HALAMAN ABSENSI (YANG SEMPAT HILANG SEKARANG KEMBALI)
    // -------------------------------------------------------------------

    public function getLeaderboardKelas(string $startDate, ?int $kelasId = null, ?string $endDate = null): array
    {
        $endDate = $endDate ?? $startDate;

        $builder = $this->select('kelas.nama_kelas, COUNT(absensi.id_absensi) as total_hadir')
            ->join('kelas', 'kelas.id_kelas = absensi.kelas_id')
            ->where('absensi.tanggal >=', $startDate)
            ->where('absensi.tanggal <=', $endDate)
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
