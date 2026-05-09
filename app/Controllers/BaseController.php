<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var \CodeIgniter\HTTP\IncomingRequest|\CodeIgniter\HTTP\CLIRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['form', 'url', 'geo', 'security'];

    /**
     * Komponen global dengan Strict Type-Hinting untuk VSCode Intelephense
     */
    protected \CodeIgniter\Session\Session $session;
    protected \CodeIgniter\Validation\ValidationInterface $validation;
    protected \CodeIgniter\Database\BaseConnection $db;

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Inisialisasi layanan global
        $this->session    = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db         = \Config\Database::connect();
    }
}
