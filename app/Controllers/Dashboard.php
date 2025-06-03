<?php namespace App\Controllers;

use App\Models\M_Tiket;
use CodeIgniter\Controller;
use App\Models\TicketModel;

class Dashboard extends Controller
{
    protected $ticketModel;

    public function __construct()
    {
        helper(['url', 'session']);
        $this->ticketModel = new M_Tiket();
    }

    public function index()
    {
        $session = session();
        $roleId = $session->get('role_id');
        $userId = $session->get('user_id');

        if (!$roleId) {
            return redirect('/');
        }

        $data = ['roleId' => $roleId];

        // Data khusus untuk Requestor (role 6)
        if ($roleId == 6) {
            $totalIT = $this->ticketModel->where('requestor_id', $userId)
                ->where('assigned_unit', 'IT')
                ->countAllResults(false);

            $totalGA = $this->ticketModel->where('requestor_id', $userId)
                ->where('assigned_unit', 'GA')
                ->countAllResults(false);

            $statusCounts = $this->ticketModel->select('status, COUNT(*) as total')
                ->where('requestor_id', $userId)
                ->groupBy('status')
                ->findAll();

            $data['totalIT'] = $totalIT;
            $data['totalGA'] = $totalGA;
            $data['statusCounts'] = $statusCounts;
        }

        // Contoh: untuk Admin (role 1), bisa kasih total user atau tiket seluruhnya
        if ($roleId == 1) {
            $totalTickets = $this->ticketModel->countAllResults(false);
            $data['totalTickets'] = $totalTickets;
            // Bisa tambah data lain sesuai kebutuhan
        }

        // Untuk role lain bisa ditambah kondisional di sini...

        return view('dashboard/index', $data);
    }
}
