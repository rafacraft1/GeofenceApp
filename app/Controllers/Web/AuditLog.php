<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

class AuditLog extends BaseController
{
    protected AuditLogModel $auditModel;

    public function __construct()
    {
        $this->auditModel = new AuditLogModel();
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $modulFilter  = (string) $this->request->getGet('module');
        $actionFilter = (string) $this->request->getGet('action');
        $startDate    = (string) $this->request->getGet('start_date');
        $endDate      = (string) $this->request->getGet('end_date');
        $search       = (string) $this->request->getGet('search');

        $builder = $this->auditModel->select('audit_logs.*, users.nama_lengkap as user_name')
            ->join('users', 'users.id_user = audit_logs.user_id', 'left')
            ->orderBy('audit_logs.created_at', 'DESC');

        if (!empty($modulFilter)) {
            $builder->where('audit_logs.module', $modulFilter);
        }

        if (!empty($actionFilter)) {
            $builder->where('audit_logs.action', $actionFilter);
        }

        if (!empty($startDate)) {
            $builder->where('DATE(audit_logs.created_at) >=', $startDate);
        }

        if (!empty($endDate)) {
            $builder->where('DATE(audit_logs.created_at) <=', $endDate);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('users.nama_lengkap', $search)
                ->orLike('audit_logs.ip_address', $search)
                ->groupEnd();
        }

        $perPage = 20;
        $page    = (int) ($this->request->getGet('page') ?? 1);

        $data = [
            'title'        => 'Audit Trail & Log Aktivitas',
            'logs'         => $builder->paginate($perPage, 'default'),
            'pager_links'  => $this->auditModel->pager->links('default', 'tailwind_pagination'),
            'total_data'   => $this->auditModel->pager->getTotal('default'),
            'page'         => $page,
            'perPage'      => $perPage,
            'modul_aktif'  => $modulFilter,
            'action_aktif' => $actionFilter,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'search'       => $search
        ];

        return view('web/audit_log', $data);
    }
}
