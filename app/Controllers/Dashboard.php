<?php

namespace App\Controllers;

use App\Models\M_Tiket;
use CodeIgniter\Controller;
use CodeIgniter\Database\RawSql;

class Dashboard extends Controller
{
    protected $ticketModel;

    public function __construct()
    {
        helper(['url', 'session']);
        $this->ticketModel = new M_Tiket(); // Menggunakan model M_Tiket untuk mengakses data tiket
    }

    public function index()
    {
        $session = session();
        $userId = $session->get('user_id');
        $idPegawai = $session->get('id_pegawai');

        if (!$userId) {
            return redirect('/'); // Redirect jika user tidak login
        }

        // Total Tiket yang Diajukan oleh Requestor
        $totalTiketUser = $this->ticketModel->where('id_pegawai_requestor', $idPegawai)
            ->countAllResults(false);

        // Total Tiket yang Diteruskan ke Unit (Finance dan GA)
        $totalTiketToUnit = $this->ticketModel->select('id_unit_tujuan, COUNT(*) as total')
            ->where('id_pegawai_requestor', $idPegawai)
            ->whereIn('id_unit_tujuan', ['E13', 'E21']) // E13 untuk Finance dan E21 untuk GA
            ->groupBy('id_unit_tujuan') // Mengelompokkan berdasarkan id_unit_tujuan
            ->findAll();

        // Inisialisasi total tiket untuk Unit Finance (E13) dan Unit GA (E21)
        $totalTiketToUnitF = 0;
        $totalTiketToUnitG = 0;

        // Memisahkan hasil berdasarkan id_unit_tujuan
        foreach ($totalTiketToUnit as $ticket) {
            if ($ticket['id_unit_tujuan'] == 'E13') {
                $totalTiketToUnitF += $ticket['total']; // Jumlah tiket untuk Unit Finance
            } elseif ($ticket['id_unit_tujuan'] == 'E21') {
                $totalTiketToUnitG += $ticket['total']; // Jumlah tiket untuk Unit GA
            }
        }

        // Status Tiket (Open, In Progress, Closed, Done)
        $statusCounts = $this->ticketModel->select('status, COUNT(*) as total')
            ->where('id_pegawai_requestor', $idPegawai)
            ->groupBy('status')
            ->findAll();

        // Jika tidak ada data status, set default
        if (empty($statusCounts)) {
            $statusCounts = [
                ['status' => 'Open', 'total' => 0],
                ['status' => 'In Progress', 'total' => 0],
                ['status' => 'Closed', 'total' => 0],
                ['status' => 'Done', 'total' => 0],
            ];
        }

        // Mengurutkan status berdasarkan urutan yang diinginkan
        $statusOrder = ['Open', 'In Progress', 'Done', 'Closed'];
        usort($statusCounts, function ($a, $b) use ($statusOrder) {
            return array_search($a['status'], $statusOrder) - array_search($b['status'], $statusOrder);
        });

        $totalTiketUnresolved = $this->ticketModel->where('id_pegawai_requestor', $idPegawai)
            ->whereIn('status', ['Open', 'In Progress'])
            ->countAllResults(false);


        $unitKerjaId = $session->get('unit_kerja_id');


        $bulanLabels = [];
        $jumlahTiketBulan = [];
        $avgTime = 0;
        $avgService = 0;

        if (in_array($unitKerjaId, ['E13', 'E21'])) {
            // Tiket per bulan
            $tiketPerBulan = $this->ticketModel
                ->select("MONTH(created_at) as bulan, COUNT(*) as total")
                ->where('YEAR(created_at)', date('Y'))
                ->where('id_unit_tujuan IS NOT NULL')
                ->groupBy('MONTH(created_at)')
                ->orderBy('MONTH(created_at)')
                ->findAll();

            for ($i = 1; $i <= 12; $i++) {
                $bulanLabels[] = date('F', mktime(0, 0, 0, $i, 1));
                $jumlahTiketBulan[] = 0;
            }

            foreach ($tiketPerBulan as $row) {
                $index = (int)$row['bulan'] - 1;
                $jumlahTiketBulan[$index] = $row['total'];
            }

            // Rating
            $ratingAvg = $this->ticketModel
                ->select('AVG(rating_time) as avg_time, AVG(rating_service) as avg_service')
                ->whereIn('id_unit_tujuan', ['E13', 'E21'])
                ->get()
                ->getRow();

            $avgTime = round($ratingAvg->avg_time ?? 0, 2);
            $avgService = round($ratingAvg->avg_service ?? 0, 2);
        }

        $data = [
            'unit_kerja_id' => $unitKerjaId,
            'statusCounts' => $statusCounts,
            'totalTiketUser' => $totalTiketUser,
            'totalTiketToUnitF' => $totalTiketToUnitF,
            'totalTiketToUnitG' => $totalTiketToUnitG,
            'totalTiketUnresolved' => $totalTiketUnresolved,
            'bulanLabels' => $bulanLabels,
            'jumlahTiketBulan' => $jumlahTiketBulan,
            'avgTime' => $avgTime,
            'avgService' => $avgService,
        ];

        return view('dashboard/index', $data);
    }
}
