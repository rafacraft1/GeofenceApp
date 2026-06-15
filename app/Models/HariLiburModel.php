<?php

namespace App\Models;

use CodeIgniter\Model;

class HariLiburModel extends Model
{
    protected $table            = 'hari_libur';
    protected $primaryKey       = 'id_libur';
    protected $returnType       = 'array';
    protected $protectFields    = true;

    // Tipe libur didaftarkan di sini
    protected $allowedFields    = ['tanggal', 'keterangan', 'tipe_libur'];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';

    protected array $tempOldData = [];
    protected $afterInsert       = ['auditInsert'];
    protected $beforeDelete      = ['auditBeforeDelete'];
    protected $afterDelete       = ['auditAfterDelete'];

    protected function auditInsert(array $data): array
    {
        if (!isset($data['result']) || !$data['result']) return $data;
        $auditService = new \App\Services\AuditService();
        $auditService->log('INSERT', $this->table, null, (array) ($data['data'] ?? []));
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
