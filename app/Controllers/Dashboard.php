<?php

namespace App\Controllers;

use App\Models\M_Tiket;
use CodeIgniter\Controller;

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


        // Mengirim data ke view
        $data = [
            'statusCounts' => $statusCounts,
            'totalTiketUser' => $totalTiketUser,
            'totalTiketToUnitF' => $totalTiketToUnitF,
            'totalTiketToUnitG' => $totalTiketToUnitG,
            'totalTiketUnresolved' => $totalTiketUnresolved,
        ];

        return view('dashboard/index', $data);
    }
}
