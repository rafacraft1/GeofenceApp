<?php

namespace App\Models;

use CodeIgniter\Model;

class PengajuanIzinModel extends Model
{
    protected $table            = 'pengajuan_izin';
    protected $primaryKey       = 'id_izin';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'siswa_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'alasan',
        'bukti_foto',
        'status',
        'catatan_penolakan',   // Alasan penolakan dari admin
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Menghitung jumlah pengajuan berdasarkan status.
     * Digunakan untuk menampilkan counter badge di halaman index.
     */
    public function getStatusCounts(?int $kelasId = null): array
    {
        $builder = $this->db->table('pengajuan_izin')
            ->select('pengajuan_izin.status, COUNT(*) as jumlah')
            ->join('siswa', 'siswa.id_siswa = pengajuan_izin.siswa_id')
            ->groupBy('pengajuan_izin.status');

        if ($kelasId !== null) {
            $builder->where('siswa.kelas_id', $kelasId);
        }

        $results = $builder->get()->getResultArray();
        $counts  = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0];

        foreach ($results as $row) {
            if (array_key_exists($row['status'], $counts)) {
                $counts[$row['status']] = (int) $row['jumlah'];
            }
        }

        return $counts;
    }

    /**
     * Query paginated pengajuan izin dengan semua filter.
     */
    public function getPaginatedIzin(
        ?int $kelasId       = null,
        ?string $search     = null,
        int $perPage        = 20,
        string $sortCol     = 'created_at',
        string $sortDir     = 'desc',
        ?string $statusFilter = null,
        ?string $dateFrom   = null,
        ?string $dateTo     = null
    ) {
        $builder = $this->select('pengajuan_izin.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = pengajuan_izin.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if ($kelasId !== null) {
            $builder->where('siswa.kelas_id', $kelasId);
        }

        if (!empty($statusFilter)) {
            $builder->where('pengajuan_izin.status', $statusFilter);
        }

        if (!empty($dateFrom)) {
            $builder->where('pengajuan_izin.tanggal_mulai >=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $builder->where('pengajuan_izin.tanggal_mulai <=', $dateTo);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('siswa.nama_siswa', $search)
                ->orLike('siswa.nis', $search)
                ->orLike('pengajuan_izin.alasan', $search)
                ->groupEnd();
        }

        $columnMap = [
            'nama_siswa'    => 'siswa.nama_siswa',
            'tanggal_mulai' => 'pengajuan_izin.tanggal_mulai',
            'status'        => 'pengajuan_izin.status',
            'created_at'    => 'pengajuan_izin.created_at'
        ];
        $targetCol = $columnMap[$sortCol] ?? 'pengajuan_izin.created_at';

        // Default sort: Pending selalu di atas, lalu terbaru
        if ($sortCol === 'created_at' && $sortDir === 'desc' && empty($statusFilter)) {
            $builder->orderBy("FIELD(pengajuan_izin.status, 'Pending', 'Approved', 'Rejected')", 'ASC', false);
            $builder->orderBy('pengajuan_izin.created_at', 'DESC');
        } else {
            $builder->orderBy($targetCol, $sortDir);
        }

        return $builder->paginate($perPage, 'default');
    }
}
