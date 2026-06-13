<?php

namespace App\Services;

use App\Models\AuditLogModel;
use Config\Services;

class AuditService
{
    protected AuditLogModel $auditModel;

    public function __construct()
    {
        $this->auditModel = new AuditLogModel();
    }

    /**
     * @param string $action
     * @param string $module
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return void
     */
    public function log(string $action, string $module, ?array $oldValues = null, ?array $newValues = null): void
    {
        $request = Services::request();
        $session = Services::session();

        $userId    = $session->get('id_user') ? (int) $session->get('id_user') : null;
        $ipAddress = (string) $request->getIPAddress();
        $userAgent = (string) $request->getUserAgent();

        $data = [
            'user_id'    => $userId,
            'action'     => $action,
            'module'     => $module,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ];

        $this->auditModel->insert($data);
    }
}
