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

    // Membatasi kolom yang bisa diisi dari luar untuk keamanan
    protected $allowedFields    = [
        'siswa_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'alasan',
        'bukti_foto',
        'status'
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * OPTIMASI BARU: Memisahkan logika query dengan Pagination, Search & Sort
     */
    public function getPaginatedIzin(?int $kelasId = null, ?string $search = null, int $perPage = 20, string $sortCol = 'created_at', string $sortDir = 'desc')
    {
        $builder = $this->select('pengajuan_izin.*, siswa.nama_siswa, siswa.nis, kelas.nama_kelas')
            ->join('siswa', 'siswa.id_siswa = pengajuan_izin.siswa_id')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        // Filter proteksi khusus Wali Kelas
        if ($kelasId !== null) {
            $builder->where('siswa.kelas_id', $kelasId);
        }

        // Fitur Pencarian (Search)
        if (!empty($search)) {
            $builder->groupStart()
                ->like('siswa.nama_siswa', $search)
                ->orLike('siswa.nis', $search)
                ->orLike('pengajuan_izin.alasan', $search)
                ->groupEnd();
        }

        // Mapping agar kolom tidak ambigous saat disortir
        $columnMap = [
            'nama_siswa'    => 'siswa.nama_siswa',
            'tanggal_mulai' => 'pengajuan_izin.tanggal_mulai',
            'status'        => 'pengajuan_izin.status',
            'created_at'    => 'pengajuan_izin.created_at'
        ];
        $targetCol = $columnMap[$sortCol] ?? 'pengajuan_izin.created_at';

        // Trik UX: Jika sort default (created_at desc), paksa status 'Pending' berada di urutan paling atas
        if ($sortCol === 'created_at' && $sortDir === 'desc') {
            $builder->orderBy("FIELD(pengajuan_izin.status, 'Pending', 'Approved', 'Rejected')", '', false);
        }

        return $builder->orderBy($targetCol, $sortDir)->paginate($perPage, 'default');
    }
}
