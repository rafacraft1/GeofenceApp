<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $request;
    protected $helpers = ['form', 'url', 'geo', 'security'];

    protected \CodeIgniter\Session\Session $session;
    protected \CodeIgniter\Validation\ValidationInterface $validation;
    protected \CodeIgniter\Database\BaseConnection $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->session    = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db         = \Config\Database::connect();
        $this->loadGlobalMenus();
    }

    private function loadGlobalMenus(): void
    {
        $roleId = (int) $this->session->get('role_id');
        $allowedMenus = [];

        if ($roleId > 0) {
            $allowedMenus = cache()->remember('global_menus_role_' . $roleId, 3600, function () use ($roleId) {
                return $this->db->table('menus')
                    ->join('role_menus', 'role_menus.id_menu = menus.id_menu')
                    ->where('role_menus.id_role', $roleId)
                    ->where('menus.is_active', 1)
                    ->orderBy('menus.urutan', 'ASC')
                    ->get()
                    ->getResultArray();
            });
        }

        \Config\Services::renderer()->setData(['allowedMenus' => $allowedMenus]);
    }
}
