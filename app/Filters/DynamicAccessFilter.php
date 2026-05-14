<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DynamicAccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('logged_in')) {
            return;
        }

        $roleId = session()->get('role_id');
        $uri = $request->getUri();

        $segment1 = $uri->getSegment(1);
        $segment2 = $uri->getTotalSegments() > 1 ? $uri->getSegment(2) : '';
        $currentPath = $segment2 ? "$segment1/$segment2" : $segment1;

        $whitelist = ['admin/dashboard', 'admin/logout', 'admin/login_action', 'admin/login'];
        if (in_array($currentPath, $whitelist)) {
            return;
        }

        $db = \Config\Database::connect();

        $hasAccess = $db->table('role_menus')
            ->join('menus', 'menus.id_menu = role_menus.id_menu')
            ->where('role_menus.id_role', $roleId)
            ->where('menus.is_active', 1)
            ->like('menus.url', $currentPath, 'after')
            ->countAllResults();

        if ($hasAccess === 0) {
            // FIX INTELEPHENSE: Pengecekan AJAX standar protokol HTTP (Header)
            $isAjax = $request->hasHeader('X-Requested-With') && $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

            if ($isAjax) {
                return \Config\Services::response()->setJSON([
                    'status' => 403,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.'
                ]);
            }

            return redirect()->to('/admin/dashboard')->with('error', 'Akses Ditolak! Anda tidak memiliki otoritas untuk membuka modul tersebut.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
