<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * @param array $dataSiswa
 * @param array $kelasMap
 * @param bool $isWaliKelas
 * @param int|null $waliKelasId
 * @return array
 */
class SiswaModel extends Model
{
    protected $table            = 'siswa';
    protected $primaryKey       = 'id_siswa';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'kelas_id',
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

    public function getSiswaWithKelas(?string $id = null)
    {
        $this->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if ($id) {
            return $this->where('id_siswa', $id)->first();
        }
        return $this->findAll();
    }

    public function getPaginatedSiswa(?string $kelasFilter, ?string $searchFilter, int $perPage, string $sortCol = 'nama_siswa', string $sortDir = 'asc')
    {
        $this->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelasFilter)) {
            $this->where('siswa.kelas_id', $kelasFilter);
        }

        if (!empty($searchFilter)) {
            $this->groupStart()
                ->like('siswa.nama_siswa', $searchFilter)
                ->orLike('siswa.nis', $searchFilter)
                ->groupEnd();
        }

        $allowedColumns = [
            'nama_siswa'  => 'siswa.nama_siswa',
            'nis'         => 'siswa.nis',
            'nama_kelas'  => 'kelas.nama_kelas',
            'fraud_count' => 'siswa.fraud_count',
            'device_id'   => 'siswa.device_id'
        ];

        $orderColumn = $allowedColumns[$sortCol] ?? 'siswa.nama_siswa';
        $orderDir    = strtolower($sortDir) === 'desc' ? 'DESC' : 'ASC';

        return $this->orderBy($orderColumn, $orderDir)
            ->paginate($perPage, 'default');
    }

    public function getSiswaForExport(?int $kelasId = null): array
    {
        $this->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if ($kelasId !== null) {
            $this->where('siswa.kelas_id', $kelasId);
        }

        return $this->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->findAll();
    }

    public function processBulkImport(array $dataSiswa, array $kelasMap, bool $isWaliKelas, ?int $waliKelasId): array
    {
        $skipped      = 0;
        $dataToInsert = [];

        $existingSiswa = $this->select('nis')->findAll();
        $existingNis   = array_column($existingSiswa, 'nis');

        foreach ($dataSiswa as $index => $row) {
            if ($index < 3) continue;

            $nis       = isset($row[0]) ? preg_replace('/\s+/', '', (string)$row[0]) : '';
            $nama      = isset($row[1]) ? trim((string)$row[1]) : '';
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
                'password'   => password_hash($nis, PASSWORD_BCRYPT)
            ];

            $existingNis[] = $nis;
        }

        if (!empty($dataToInsert)) {
            $this->db->transStart();
            $this->insertBatch($dataToInsert);
            $this->db->transComplete();

            return [
                'status'   => $this->db->transStatus(),
                'inserted' => count($dataToInsert),
                'skipped'  => $skipped
            ];
        }

        return [
            'status'   => true,
            'inserted' => 0,
            'skipped'  => $skipped
        ];
    }
}
