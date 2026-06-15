<?php

namespace App\Models;

use CodeIgniter\Model;

class KelasModel extends Model
{
    protected $table            = 'kelas';
    protected $primaryKey       = 'id_kelas';
    protected $returnType       = 'array';
    protected $protectFields    = true;

    // Pastikan zona_id ada di dalam allowedFields agar bisa di-insert/update
    protected $allowedFields    = ['nama_kelas', 'wali_kelas_id', 'zona_id', 'created_at', 'updated_at'];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // Konfigurasi Audit Trail (Riwayat Perubahan Data)
    protected array $tempOldData = [];
    protected $afterInsert       = ['auditInsert'];
    protected $beforeUpdate      = ['auditBeforeUpdate'];
    protected $afterUpdate       = ['auditAfterUpdate'];
    protected $beforeDelete      = ['auditBeforeDelete'];
    protected $afterDelete       = ['auditAfterDelete'];

    /**
     * Mengambil daftar kelas beserta nama Wali Kelas, nama Zona PKL, 
     * dan jumlah siswa yang terdaftar di dalamnya (Mendukung Pencarian & Pagination)
     */
    public function getPaginatedKelas(string $search = '', int $perPage = 10)
    {
        $this->select('kelas.*, users.nama_lengkap as nama_wali, zona_absensi.nama_zona, COUNT(siswa.id_siswa) as jumlah_siswa');
        $this->join('users', 'users.id_user = kelas.wali_kelas_id', 'left');
        $this->join('zona_absensi', 'zona_absensi.id_zona = kelas.zona_id', 'left');
        // Gabungkan dengan siswa untuk menghitung jumlah siswa per kelas
        $this->join('siswa', 'siswa.kelas_id = kelas.id_kelas', 'left');

        if (!empty($search)) {
            $this->groupStart()
                ->like('kelas.nama_kelas', $search)
                ->orLike('users.nama_lengkap', $search)
                ->orLike('zona_absensi.nama_zona', $search)
                ->groupEnd();
        }

        $this->groupBy('kelas.id_kelas');
        $this->orderBy('kelas.nama_kelas', 'ASC');

        return $this->paginate($perPage, 'default');
    }

    // -------------------------------------------------------------------------
    // FUNGSI AUDIT TRAIL OTOMATIS
    // -------------------------------------------------------------------------

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
}
