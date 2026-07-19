<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\AbsensiModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    protected SiswaModel $siswaModel;
    protected AbsensiModel $absensiModel;

    public function __construct()
    {
        $this->siswaModel   = new SiswaModel();
        $this->absensiModel = new AbsensiModel();
    }

    /**
     * Resolves filter parameter into startDate and endDate strings.
     *
     * @param string $filter   One of: hari_ini, minggu_ini, bulan_ini, custom
     * @param string|null $start  Custom start date (Y-m-d)
     * @param string|null $end    Custom end date (Y-m-d)
     * @return array{startDate: string, endDate: string, days: int}
     */
    private function resolveRange(string $filter, ?string $start, ?string $end): array
    {
        $sekarang = Time::now('Asia/Jakarta');
        $hariIni  = $sekarang->toDateString();

        switch ($filter) {
            case 'minggu_ini':
                $startDate = Time::now('Asia/Jakarta')->subDays(6)->toDateString();
                $endDate   = $hariIni;
                $days      = 7;
                break;

            case 'bulan_ini':
                $startDate = Time::now('Asia/Jakarta')->subDays(29)->toDateString();
                $endDate   = $hariIni;
                $days      = 30;
                break;

            case 'custom':
                // Validate custom dates
                $startDate = ($start && preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) ? $start : $hariIni;
                $endDate   = ($end && preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) ? $end : $hariIni;
                // Ensure startDate <= endDate
                if ($startDate > $endDate) {
                    [$startDate, $endDate] = [$endDate, $startDate];
                }
                $days = (int) ((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
                break;

            default: // 'hari_ini'
                $startDate = $hariIni;
                $endDate   = $hariIni;
                $days      = 1;
                break;
        }

        return compact('startDate', 'endDate', 'days');
    }

    /**
     * Builds chart data arrays for the given date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $days
     * @param int|null $kelasId
     * @return array
     */
    private function buildChartData(string $startDate, string $endDate, int $days, ?int $kelasId): array
    {
        $grafikLabels     = [];
        $grafikHadir      = array_fill(0, $days, 0);
        $grafikTerlambat  = array_fill(0, $days, 0);
        $grafikAlpa       = array_fill(0, $days, 0);
        $grafikIzin       = array_fill(0, $days, 0);
        $grafikSakit      = array_fill(0, $days, 0);
        $grafikDispensasi = array_fill(0, $days, 0);
        $dates            = [];

        for ($i = 0; $i < $days; $i++) {
            $tgl = date('Y-m-d', strtotime($startDate . ' +' . $i . ' days'));
            $dates[]        = $tgl;
            $grafikLabels[] = date('d M', strtotime($tgl));
        }

        $rekapTrend = $this->absensiModel->getTrendKehadiran($startDate, $endDate, $kelasId);

        foreach ($rekapTrend as $row) {
            $idx = array_search($row['tanggal'], $dates);
            if ($idx !== false) {
                if ($row['status'] === 'Hadir') {
                    $grafikHadir[$idx] += (int) $row['total'];
                } elseif ($row['status'] === 'Terlambat') {
                    $grafikTerlambat[$idx] += (int) $row['total'];
                } elseif ($row['status'] === 'Alpa') {
                    $grafikAlpa[$idx] += (int) $row['total'];
                } elseif ($row['status'] === 'Izin') {
                    $grafikIzin[$idx] += (int) $row['total'];
                } elseif ($row['status'] === 'Sakit') {
                    $grafikSakit[$idx] += (int) $row['total'];
                } elseif ($row['status'] === 'Dispensasi') {
                    $grafikDispensasi[$idx] += (int) $row['total'];
                }
            }
        }

        return [
            'labels'     => $grafikLabels,
            'hadir'      => $grafikHadir,
            'terlambat'  => $grafikTerlambat,
            'alpa'       => $grafikAlpa,
            'izin'       => $grafikIzin,
            'sakit'      => $grafikSakit,
            'dispensasi' => $grafikDispensasi,
        ];
    }

    /**
     * Main dashboard page. Renders initial view with default filter (30 hari).
     *
     * @return mixed
     */
    public function index()
    {
        $sekarang    = Time::now('Asia/Jakarta');
        $hariIni     = $sekarang->toDateString();

        $isWaliKelas = session()->get('is_wali_kelas');
        $kelasId     = $isWaliKelas ? session()->get('kelas_id') : null;

        // Default: 30 hari ke belakang
        $startDate = Time::now('Asia/Jakarta')->subDays(29)->toDateString();
        $endDate   = $hariIni;
        $days      = 30;

        // Stat cards tetap menampilkan data hari ini saat load awal
        if ($isWaliKelas) {
            $this->siswaModel->where('kelas_id', $kelasId);
        }
        $totalSiswa = $this->siswaModel->countAllResults();

        $stats      = $this->absensiModel->getDashboardStats($hariIni, $kelasId);
        $distribusi = $this->absensiModel->getDashboardDistribution($hariIni, $kelasId);
        $manipulasi = $this->absensiModel->getFraudList($hariIni, $kelasId);
        $topClasses = $this->absensiModel->getLeaderboardKelas($hariIni, $kelasId);

        $hadirHariIni = $stats['hadir'];
        $persenHadir  = ($totalSiswa > 0) ? round(($hadirHariIni / $totalSiswa) * 100) : 0;

        $chartData = $this->buildChartData($startDate, $endDate, $days, $kelasId);

        $data = [
            'title'              => 'Dashboard Analytics',
            'is_wali_kelas'      => $isWaliKelas,
            'total_siswa'        => $totalSiswa,
            'hadir_hari_ini'     => $hadirHariIni,
            'alpa_hari_ini'      => $stats['alpa'],
            'fraud_hari_ini'     => $stats['fraud'],
            'persen_hadir'       => $persenHadir,
            'chart_distribution' => json_encode($distribusi),
            'top_classes'        => $topClasses,
            'chart_labels'       => json_encode($chartData['labels']),
            'chart_hadir'        => json_encode($chartData['hadir']),
            'chart_terlambat'    => json_encode($chartData['terlambat']),
            'chart_alpa'         => json_encode($chartData['alpa']),
            'chart_izin'         => json_encode($chartData['izin']),
            'chart_sakit'        => json_encode($chartData['sakit']),
            'chart_dispensasi'   => json_encode($chartData['dispensasi']),
            'list_manipulasi'    => $manipulasi,
            'default_filter'     => 'bulan_ini',
        ];

        return view('web/dashboard', $data);
    }

    /**
     * AJAX endpoint — returns JSON dashboard data for a given date range.
     * GET /admin/dashboard/data?filter=hari_ini|minggu_ini|bulan_ini|custom&start=Y-m-d&end=Y-m-d
     *
     * @return ResponseInterface
     */
    public function getData(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $filter    = $this->request->getGet('filter') ?? 'bulan_ini';
        $start     = $this->request->getGet('start');
        $end       = $this->request->getGet('end');

        $isWaliKelas = session()->get('is_wali_kelas');
        $kelasId     = $isWaliKelas ? (int) session()->get('kelas_id') : null;

        ['startDate' => $startDate, 'endDate' => $endDate, 'days' => $days] = $this->resolveRange($filter, $start, $end);

        if ($isWaliKelas) {
            $this->siswaModel->where('kelas_id', $kelasId);
        }
        $totalSiswa = $this->siswaModel->countAllResults();

        $stats      = $this->absensiModel->getDashboardStats($startDate, $kelasId, $endDate);
        $distribusi = $this->absensiModel->getDashboardDistribution($startDate, $kelasId, $endDate);
        $manipulasi = $this->absensiModel->getFraudList($startDate, $kelasId, $endDate);
        $topClasses = $this->absensiModel->getLeaderboardKelas($startDate, $kelasId, $endDate);

        $hadirTotal  = $stats['hadir'];
        $persenHadir = ($totalSiswa > 0) ? round(($hadirTotal / $totalSiswa) * 100) : 0;

        $chartData = $this->buildChartData($startDate, $endDate, $days, $kelasId);

        return $this->response->setJSON([
            'stats' => [
                'total_siswa'    => $totalSiswa,
                'hadir'          => $hadirTotal,
                'alpa'           => $stats['alpa'],
                'fraud'          => $stats['fraud'],
                'persen_hadir'   => $persenHadir,
            ],
            'chart' => $chartData,
            'distribution' => $distribusi,
            'top_classes'  => $topClasses,
            'manipulasi'   => $manipulasi,
            'range'        => [
                'start' => $startDate,
                'end'   => $endDate,
                'label' => ($startDate === $endDate)
                    ? date('d M Y', strtotime($startDate))
                    : date('d M Y', strtotime($startDate)) . ' – ' . date('d M Y', strtotime($endDate)),
            ],
        ]);
    }
}
