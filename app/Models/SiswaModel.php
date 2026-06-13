<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaModel extends Model
{
    protected $table            = 'siswa';
    protected $primaryKey       = 'id_siswa';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'kelas_id',
        'zona_id',
        'nis',
        'nama_siswa',
        'password',
        'foto_profil',
        'device_id',
        'api_token',
        'fcm_token',
        'lat_terakhir',
        'long_terakhir',
        'last_login',
        'is_blocked',
        'fraud_count'
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

    /**
     * @param array $data
     * @return array
     */
    protected function auditInsert(array $data): array
    {
        if (!isset($data['result']) || !$data['result']) return $data;
        $auditService = new \App\Services\AuditService();
        $auditService->log('INSERT', $this->table, null, (array) ($data['data'] ?? []));
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
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

    /**
     * @param array $data
     * @return array
     */
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

    /**
     * @param array $data
     * @return array
     */
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

    /**
     * @param array $data
     * @return array
     */
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

    /**
     * @param string|null $id
     * @return array|object|null
     */
    public function getSiswaWithKelas(?string $id = null)
    {
        $this->select('siswa.*, kelas.nama_kelas, zona_absensi.nama_zona')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->join('zona_absensi', 'zona_absensi.id_zona = siswa.zona_id', 'left');

        if ($id) return $this->where('siswa.id_siswa', $id)->first();
        return $this->findAll();
    }

    /**
     * @param string|null $kelasFilter
     * @param string|null $searchFilter
     * @param int $perPage
     * @param string $sortCol
     * @param string $sortDir
     * @return mixed
     */
    public function getPaginatedSiswa(?string $kelasFilter, ?string $searchFilter, int $perPage, string $sortCol = 'nama_siswa', string $sortDir = 'asc')
    {
        $this->select('siswa.*, kelas.nama_kelas, zona_absensi.nama_zona')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->join('zona_absensi', 'zona_absensi.id_zona = siswa.zona_id', 'left');

        if (!empty($kelasFilter)) $this->where('siswa.kelas_id', $kelasFilter);
        if (!empty($searchFilter)) {
            $this->groupStart()->like('siswa.nama_siswa', $searchFilter)->orLike('siswa.nis', $searchFilter)->groupEnd();
        }

        $allowedColumns = ['nama_siswa' => 'siswa.nama_siswa', 'nis' => 'siswa.nis', 'nama_kelas' => 'kelas.nama_kelas', 'fraud_count' => 'siswa.fraud_count', 'device_id' => 'siswa.device_id'];
        $orderColumn = $allowedColumns[$sortCol] ?? 'siswa.nama_siswa';
        $orderDir    = strtolower($sortDir) === 'desc' ? 'DESC' : 'ASC';

        return $this->orderBy($orderColumn, $orderDir)->paginate($perPage, 'default');
    }

    /**
     * @param int|null $kelasId
     * @return array
     */
    public function getSiswaForExport(?int $kelasId = null): array
    {
        $this->select('siswa.*, kelas.nama_kelas, zona_absensi.nama_zona')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->join('zona_absensi', 'zona_absensi.id_zona = siswa.zona_id', 'left');

        if ($kelasId !== null) $this->where('siswa.kelas_id', $kelasId);
        return $this->orderBy('kelas.nama_kelas', 'ASC')->orderBy('siswa.nama_siswa', 'ASC')->findAll();
    }

    /**
     * @param array $dataSiswa
     * @param array $kelasMap
     * @param bool $isWaliKelas
     * @param int|null $waliKelasId
     * @return array
     */
    public function processBulkImport(array $dataSiswa, array $kelasMap, bool $isWaliKelas, ?int $waliKelasId): array
    {
        $skipped = 0;
        $dataToInsert = [];
        $existingSiswa = $this->select('nis')->findAll();
        $existingNis   = array_column($existingSiswa, 'nis');

        foreach ($dataSiswa as $index => $row) {
            if ($index < 3) continue;
            $nis = isset($row[0]) ? preg_replace('/\s+/', '', (string)$row[0]) : '';
            $nama = isset($row[1]) ? trim((string)$row[1]) : '';
            $namaKelas = isset($row[2]) ? strtolower(trim((string)$row[2])) : '';

            if (empty($nis) || empty($nama) || empty($namaKelas)) continue;
            if (!isset($kelasMap[$namaKelas])) {
                $skipped++;
                continue;
            }
            $kelasIdTarget = (int) $kelasMap[$namaKelas];

            if ($isWaliKelas && $kelasIdTarget !== $waliKelasId) {
                $skipped++;
                continue;
            }
            if (in_array($nis, $existingNis)) {
                $skipped++;
                continue;
            }

            $dataToInsert[] = [
                'nis'        => $nis,
                'nama_siswa' => $nama,
                'kelas_id'   => $kelasIdTarget,
                'zona_id'    => null,
                'password'   => password_hash($nis, PASSWORD_BCRYPT)
            ];
            $existingNis[] = $nis;
        }

        if (!empty($dataToInsert)) {
            $this->db->transStart();
            $this->insertBatch($dataToInsert);
            $this->db->transComplete();
            return ['status' => $this->db->transStatus(), 'inserted' => count($dataToInsert), 'skipped' => $skipped];
        }
        return ['status' => true, 'inserted' => 0, 'skipped' => $skipped];
    }

    /**
     * @param string $idSiswa
     * @param int $kodeHari
     * @return array|null
     */
    public function getAturanZonaSiswa(string $idSiswa, int $kodeHari)
    {
        $builder = $this->db->table('siswa')
            ->select('
                siswa.id_siswa,
                COALESCE(z_siswa.id_zona, z_kelas.id_zona, z_default.id_zona) as id_zona,
                COALESCE(z_siswa.nama_zona, z_kelas.nama_zona, z_default.nama_zona) as nama_zona,
                COALESCE(z_siswa.latitude, z_kelas.latitude, z_default.latitude) as latitude,
                COALESCE(z_siswa.longitude, z_kelas.longitude, z_default.longitude) as longitude,
                COALESCE(z_siswa.radius, z_kelas.radius, z_default.radius) as radius,
                jadwal.waktu_buka_absen,
                jadwal.jam_masuk,
                jadwal.jam_pulang,
                jadwal.is_libur
            ')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->join('zona_absensi z_siswa', 'z_siswa.id_zona = siswa.zona_id', 'left')
            ->join('zona_absensi z_kelas', 'z_kelas.id_zona = kelas.zona_id', 'left')
            ->join('zona_absensi z_default', 'z_default.is_default = 1', 'left')
            ->join('zona_jadwal jadwal', 'jadwal.zona_id = COALESCE(z_siswa.id_zona, z_kelas.id_zona, z_default.id_zona) AND jadwal.kode_hari = ' . $kodeHari, 'left')
            ->where('siswa.id_siswa', $idSiswa);

        return $builder->get()->getRowArray();
    }
}
