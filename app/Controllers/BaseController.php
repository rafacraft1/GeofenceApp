<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 * Induk dari semua Controller Web. Menyediakan fungsi utilitas global
 * untuk menghindari redundansi kode.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * Helper global yang diload otomatis
     * @var array
     */
    protected $helpers = ['cookie', 'date', 'security'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * GLOBAL UTILITY: Proteksi Akses Wali Kelas
     * Mengunci akses agar Wali Kelas hanya bisa melihat/memodifikasi data kelasnya sendiri.
     */
    protected function checkAksesWaliKelas(int $targetKelasId): bool
    {
        if (session()->get('is_wali_kelas')) {
            return $targetKelasId === (int) session()->get('kelas_id');
        }
        return true; // Admin punya akses ke semua kelas
    }

    /**
     * GLOBAL UTILITY: Setup Konfigurasi Pagination
     * Menghilangkan duplikasi inisialisasi pagination di setiap controller.
     * * @return array{pager: \CodeIgniter\Pager\Pager, page: int, perPage: int}
     */
    protected function setupPagination(string $paramName = 'page', int $perPage = 15): array
    {
        return [
            'pager'   => \Config\Services::pager(),
            'page'    => (int) ($this->request->getGet($paramName) ?? 1),
            'perPage' => $perPage
        ];
    }
}
