<?php

namespace App\Controllers;

use App\Models\JenisPerangkatModel;
use App\Models\RoleModel;
use App\Models\RuanganModel;
use App\Models\TicketCommentModel;
use App\Models\TicketFeedbackModel;
use CodeIgniter\Controller;
use App\Models\TicketModel;
use App\Models\UserModel;
use App\Models\TicketTransferModel;
use Config\Services;

class Tickets extends Controller
{
    protected $ticketModel;
    protected $ticketTransferModel;
    protected $commentModel;
    protected $feedbackModel;


    public function __construct()
    {
        $this->ticketModel = new TicketModel();
        $this->ticketTransferModel = new TicketTransferModel();
        $this->feedbackModel = new TicketFeedbackModel();
        $this->commentModel = new TicketCommentModel();
        helper(['url', 'form', 'session']);
    }

    // Halaman daftar tiket (index view)
    public function index()
    {
        $roleId = session()->get('role_id');
        if (!$roleId) return redirect('/');

        return view('tickets/index', ['roleId' => $roleId]);
    }

    // API: ambil daftar tiket sesuai role
    public function list()
    {
        $session = session();
        $roleId = $session->get('role_id');
        $userId = $session->get('user_id');

        if ($roleId == 2) {
            $tickets = $this->ticketModel->getTicketsByUnit('IT');
        } elseif ($roleId == 3) {
            $tickets = $this->ticketModel->getTicketsByUnit('GA');
        } elseif ($roleId == 4) {
            $tickets = $this->ticketModel->getTicketsByStaff($userId);
        } elseif ($roleId == 5) {
            $tickets = $this->ticketModel->getTicketsByStaff($userId);
        } elseif ($roleId == 6) {
            $tickets = $this->ticketModel->getTicketsByRequestor($userId);
        } else {
            $tickets = [];
        }

        return $this->response->setJSON($tickets);
    }

    public function detailView($ticketId)
    {
        helper('session');
        $roleId = session()->get('role_id');
        if (!$roleId) {
            return redirect('/');
        }
        // Kirim ticketId ke view supaya bisa fetch API detail
        return view('tickets/detail', ['ticketId' => $ticketId]);
    }


    public function apiDetail($ticketId)
    {
        $ticket = $this->ticketModel->getTicketDetailById($ticketId);

        if (!$ticket) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket tidak ditemukan'])->setStatusCode(404);
        }

        return $this->response->setJSON($ticket);
    }


    // Halaman buat tiket baru, hanya role 6 (requestor)
    public function createView()
    {
        helper('session');
        $roleId = session()->get('role_id');
        if ($roleId != 6) return redirect('/tickets');


        $ruanganModel = new RuanganModel();
        $jenisPerangkatModel = new JenisPerangkatModel();

        $kategori_list = $jenisPerangkatModel->distinct()->select('kategori')->orderBy('kategori')->findAll();
        $data = [
            'ruangan_list' => $ruanganModel->getAll(),
            'kategori_list' => array_column($kategori_list, 'kategori', 'kategori'),
        ];
        return view('tickets/create', $data);
    }





    // API create tiket baru
    // API create tiket baru
    public function create()
    {
        $session = session();
        $data = $this->request->getJSON();

        // Validasi sederhana
        if (!$data->title || !$data->assigned_unit || !$data->kategori || !$data->ruangan_id || !$data->jenis_perangkat_id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Judul, Unit, Kategori, Ruangan, dan Jenis Perangkat wajib diisi']);
        }

        $newTicket = [
            'title'              => $data->title,
            'description'        => $data->description ?? '',
            'requestor_id'       => $session->get('user_id'),
            'assigned_unit'      => $data->assigned_unit,
            'kategori'           => $data->kategori,
            'ruangan_id'         => $data->ruangan_id,
            'jenis_perangkat_id' => $data->jenis_perangkat_id,
            'status'             => 'Menunggu',
        ];

        $this->ticketModel->insert($newTicket);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Tiket berhasil dibuat']);
    }


    // Halaman update tiket
    public function updateView($ticketId)
    {
        $ticket = $this->ticketModel->find($ticketId);
        if (!$ticket) return redirect('/tickets');

        return view('tickets/edit', ['ticket' => $ticket]);
    }

    // API update status tiket
    public function updateStatus()
    {
        $session = session();
        $data = $this->request->getJSON();

        if (!$data->ticket_id || !$data->status) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        $ticket = $this->ticketModel->find($data->ticket_id);
        $userId = $session->get('user_id');
        $roleId = $session->get('role_id');

        if (!$ticket) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket tidak ditemukan']);
        }

        // Requestor cuma bisa update tiket miliknya
        if ($roleId == 6 && $ticket['requestor_id'] != $userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Bukan tiket milik Anda']);
        }

        // Kepala unit (role 2 = IT, role 3 = GA) boleh update jika tiket assigned_unit sesuai
        if (in_array($roleId, [2, 3])) {
            $unitMap = [
                2 => 'IT',
                3 => 'GA'
            ];
            if ($ticket['assigned_unit'] !== $unitMap[$roleId]) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak punya hak update tiket ini']);
            }
        }
        // Pegawai (role 4,5) hanya boleh update tiket yg assigned ke mereka
        elseif (in_array($roleId, [4, 5])) {
            if ($ticket['assigned_staff_id'] != $userId) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak punya hak update tiket ini']);
            }
        }

        // Kalau kepala unit, jangan lupa set assigned_head_id juga jika mau track siapa yg update
        if (in_array($roleId, [2, 3])) {
            $updateData = [
                'status' => $data->status,
                'assigned_head_id' => $userId
            ];
        } else {
            $updateData = [
                'status' => $data->status
            ];
        }

        $this->ticketModel->update($data->ticket_id, $updateData);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Status tiket diperbarui']);
    }

    public function boardView()
    {
        $roleId = session()->get('role_id');
        if (!in_array($roleId, [2, 3])) {
            // Bukan kepala IT atau GA, tolak akses
            return redirect('/tickets');
        }

        return view('tickets/board', ['roleId' => $roleId]);
    }

    /**
     * API ambil list tiket berdasarkan status dan tujuan sesuai role kepala
     * @param string $status Status tiket: menunggu, open, inprogress, done, transfer
     */
    public function boardList($status)
    {
        $session = session();
        $roleId = $session->get('role_id');

        if (!in_array($roleId, [2, 3])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(403);
        }

        // Tentukan tujuan berdasar role
        $unit = $roleId == 2 ? 'IT' : 'GA';

        // Map status friendly ke db
        $statusMap = [
            'menunggu' => 'Menunggu',
            'open' => 'Open',
            'inprogress' => 'In Progress',
            'done' => 'Done',
            'transfer' => 'Transfer',
        ];

        if (!array_key_exists($status, $statusMap)) {
            return $this->response->setJSON([]);
        }

        $tickets = $this->ticketModel
            ->select('tickets.*, ruangan.nama as ruangan_name')
            ->join('ruangan', 'ruangan.id = tickets.ruangan_id', 'left')
            ->where('assigned_unit', $unit)
            ->where('status', $statusMap[$status])
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->response->setJSON($tickets);
    }

    public function getStaffByUnit($unit)
    {
        $userModel = new UserModel();
        // Ambil user dengan role pegawai IT(4) atau GA(5) sesuai unit
        if ($unit === 'IT') {
            $staff = $userModel->where('role_id', 4)->findAll();
        } elseif ($unit === 'GA') {
            $staff = $userModel->where('role_id', 5)->findAll();
        } else {
            $staff = [];
        }

        return $this->response->setJSON($staff);
    }
    public function assign()
    {
        $session = session();
        $roleId = $session->get('role_id');
        $data = $this->request->getJSON();

        if (!in_array($roleId, [2, 3])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda bukan kepala unit']);
        }

        if (!$data->ticket_id || !$data->assigned_staff_id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        $updateData = [
            'assigned_staff_id' => $data->assigned_staff_id,
            'assigned_head_id' => $session->get('user_id'),
            'status' => 'Open',
            'deadline' => $data->deadline,
        ];

        $this->ticketModel->update($data->ticket_id, $updateData);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Tiket berhasil ditugaskan']);
    }

    public function boardStaffView()
    {
        $session = session();
        $roleId = $session->get('role_id');
        $userId = $session->get('user_id');
        return view('tickets/board_staff', ['roleId' => $roleId, 'userId' => $userId]);
    }



    public function takeTicket()
    {
        $session = session();
        $roleId = $session->get('role_id');
        $userId = $session->get('user_id');
        $data = $this->request->getJSON();

        if (!$data->ticket_id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Ticket ID diperlukan']);
        }

        $ticket = $this->ticketModel->find($data->ticket_id);

        if (!$ticket) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket tidak ditemukan']);
        }

        // Pastikan pegawai yang ambil adalah assigned_staff_id dan status tiket open
        if ($ticket['assigned_staff_id'] != $userId || $ticket['status'] != 'Open') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak punya hak untuk mengambil tiket ini']);
        }

        $this->ticketModel->update($data->ticket_id, ['status' => 'In Progress']);

        // Jika ada transfer, set accepted
        $this->ticketTransferModel->where('ticket_id', $data->ticket_id)
            ->where('to_staff_id', $userId)
            ->where('status', 'pending')
            ->set(['status' => 'accepted', 'updated_at' => date('Y-m-d H:i:s')])
            ->update();

        return $this->response->setJSON(['status' => 'success', 'message' => 'Tiket berhasil diambil']);
    }



    public function transferTicket()
    {
        $session = session();
        $userId = $session->get('user_id');
        $data = $this->request->getJSON();

        if (!$data->ticket_id || !$data->to_staff_id || !$data->reason) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        $ticket = $this->ticketModel->find($data->ticket_id);
        if (!$ticket) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket tidak ditemukan']);
        }

        if ($ticket['assigned_staff_id'] != $userId || strtolower($ticket['status']) != 'open') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak punya hak untuk mengalihkan tiket ini']);
        }

        // Insert record transfer
        $this->ticketTransferModel->insert([
            'ticket_id' => $data->ticket_id,
            'from_staff_id' => $userId,
            'to_staff_id' => $data->to_staff_id,
            'reason' => $data->reason,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update ticket status jadi Transfer
        $this->ticketModel->update($data->ticket_id, ['status' => 'Transfer']);

        // Load models
        $email = \Config\Services::email();
        $userModel = new UserModel();

        // Ambil email dan nama staff tujuan
        $toStaff = $userModel->find($data->to_staff_id);

        // Cari kepala unit berdasarkan assigned_unit tiket dan role_id kepala unit (misal 2 = Kepala IT, 3 = Kepala GA)
        // Asumsikan unit dan role_id mapping seperti ini, sesuaikan dengan struktur DB-mu
        $unitToRoleIdMap = [
            'IT' => 2,
            'GA' => 3,
        ];
        $unit = $ticket['assigned_unit'];
        $kepalaUnitRoleId = $unitToRoleIdMap[$unit] ?? null;

        $kepalaUnit = null;
        if ($kepalaUnitRoleId !== null) {
            // Cari kepala unit user berdasarkan role_id
            $kepalaUnit = $userModel->where('role_id', $kepalaUnitRoleId)->first();
        }

        // Kirim email ke staff tujuan
        if ($toStaff && !empty($toStaff['email'])) {
            $email->clear();
            $email->setFrom('fahren21ti@mahasiswa.pcr.ac.id', 'Help Desk Apps');
            $email->setTo($toStaff['email']);
            $email->setSubject('Notifikasi Pengalihan Tiket');
            $messageStaff = "
            Halo {$toStaff['full_name']},<br><br>
            Anda mendapatkan pengalihan tiket baru dengan judul: <strong>{$ticket['title']}</strong>.<br>
            Alasan pengalihan: {$data->reason}<br><br>
            Silakan cek sistem Help Desk untuk detail lebih lanjut.<br><br>
            Terima kasih.
        ";
            $email->setMessage($messageStaff);
            $email->send();
        }

        // Kirim email ke kepala unit
        if ($kepalaUnit && !empty($kepalaUnit['email'])) {
            $email->clear();
            $email->setFrom('fahren21ti@mahasiswa.pcr.ac.id', 'Help Desk Apps');
            $email->setTo($kepalaUnit['email']);
            $email->setSubject('Notifikasi Pengalihan Tiket - Kepala Unit');
            $messageKepala = "
            Halo {$kepalaUnit['full_name']},<br><br>
            Tiket berjudul <strong>{$ticket['title']}</strong> telah dialihkan ke pegawai {$toStaff['full_name']} dengan alasan:<br>
            <em>{$data->reason}</em><br><br>
            Silakan cek sistem Help Desk untuk informasi lebih lanjut.<br><br>
            Terima kasih.
        ";
            $email->setMessage($messageKepala);
            $email->send();
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Tiket berhasil dialihkan dan email notifikasi terkirim']);
    }


    // Pegawai respon transfer: terima atau tolak
    public function respondTransfer()
    {
        $session = session();
        $userId = $session->get('user_id');
        $data = $this->request->getJSON();

        if (!$data->transfer_id || !$data->response) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        $transfer = $this->ticketTransferModel->find($data->transfer_id);
        if (!$transfer || $transfer['to_staff_id'] != $userId || $transfer['status'] != 'pending') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Transfer tidak ditemukan atau sudah diproses']);
        }

        if ($data->response === 'accept') {
            // Update ticket penanggung jawab dan status
            $this->ticketModel->update($transfer['ticket_id'], [
                'assigned_staff_id' => $userId,
                'status' => 'In Progress',
            ]);
            // Update transfer status
            $this->ticketTransferModel->update($transfer['id'], [
                'status' => 'accepted',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } elseif ($data->response === 'reject') {
            // Tolak transfer, simpan alasan penolakan
            $reasonReject = $data->reason ?? '';
            $this->ticketTransferModel->update($transfer['id'], [
                'status' => 'rejected',
                'reason' => $reasonReject,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            // Update tiket status ke Menunggu supaya kepala unit assign ulang
            $this->ticketModel->update($transfer['ticket_id'], [
                'status' => 'Menunggu',
                'assigned_staff_id' => null,
                'assigned_head_id' => null
            ]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Respon tidak valid']);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Respon transfer berhasil diproses']);
    }

    // API list transfer tiket yang dialihkan ke pegawai (pending)
    public function listTransfers()
    {
        $session = session();
        $userId = $session->get('user_id');
        $transfers = $this->ticketTransferModel->getPendingTransfersForStaff($userId);

        return $this->response->setJSON($transfers);
    }

    public function staffList($status)
    {
        $session = session();
        $userId = $session->get('user_id');
        $status = strtolower($status);

        $statusDbMap = [
            'open' => 'Open',
            'menunggu' => 'Menunggu',
            'inprogress' => 'In Progress',
            'done' => 'Done'
        ];

        if ($status === 'transfer') {
            $asReceiver = $this->ticketTransferModel
                ->select('tickets.*, ticket_transfers.reason as transfer_reason, ticket_transfers.id as transfer_id, ticket_transfers.from_staff_id, ticket_transfers.to_staff_id, ticket_transfers.status as transfer_status')
                ->join('tickets', 'tickets.id = ticket_transfers.ticket_id')
                ->where('ticket_transfers.to_staff_id', $userId)
                ->where('ticket_transfers.status', 'pending')
                ->orderBy('ticket_transfers.created_at', 'DESC')
                ->findAll();

            $asSender = $this->ticketTransferModel
                ->select('tickets.*, ticket_transfers.reason as transfer_reason, ticket_transfers.id as transfer_id, ticket_transfers.from_staff_id, ticket_transfers.to_staff_id, ticket_transfers.status as transfer_status')
                ->join('tickets', 'tickets.id = ticket_transfers.ticket_id')
                ->where('ticket_transfers.from_staff_id', $userId)
                ->where('ticket_transfers.status', 'pending')
                ->orderBy('ticket_transfers.created_at', 'DESC')
                ->findAll();

            $tickets = array_merge($asReceiver, $asSender);
            // Hilangkan duplikat ticket_id jika perlu
            $unique = [];
            foreach ($tickets as $ticket) {
                $unique[$ticket['transfer_id']] = $ticket;
            }
            $tickets = array_values($unique);

            return $this->response->setJSON($tickets);
        }



        $statusDb = $statusDbMap[$status] ?? ucfirst($status);

        $tickets = $this->ticketModel
            ->select('tickets.*, ruangan.nama as ruangan_name, jenis_perangkat.nama as jenis_perangkat_nama, tickets.deadline')
            ->join('ruangan', 'ruangan.id = tickets.ruangan_id', 'left')
            ->join('jenis_perangkat', 'jenis_perangkat.id = tickets.jenis_perangkat_id', 'left')
            ->where('assigned_staff_id', $userId)
            ->where('status', $statusDb)
            ->orderBy('created_at', 'DESC')
            ->findAll();


        return $this->response->setJSON($tickets);
    }



    // Simpan komentar belum selesai
    public function saveComment()
    {
        $session = session();
        $userId = $session->get('user_id');
        $data = $this->request->getJSON();

        if (!$data->ticket_id || !$data->comment) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data komentar tidak lengkap']);
        }

        $commentData = [
            'ticket_id' => $data->ticket_id,
            'user_id' => $userId,
            'comment' => $data->comment,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->commentModel->insert($commentData);

        // Update status tiket jadi "Belum Selesai"
        $this->ticketModel->update($data->ticket_id, ['status' => 'Belum Selesai']);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Komentar berhasil disimpan']);
    }

    // Simpan feedback selesai
    public function saveFeedback()
    {
        $session = session();
        $requestorId = $session->get('user_id');
        $data = $this->request->getJSON();

        if (!$data->ticket_id || !isset($data->rating)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data feedback tidak lengkap']);
        }

        $feedbackData = [
            'ticket_id' => $data->ticket_id,
            'requestor_id' => $requestorId,
            'rating' => $data->rating,
            'suggestion' => $data->suggestion ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->feedbackModel->insert($feedbackData);

        // Update status tiket jadi "Selesai"
        $this->ticketModel->update($data->ticket_id, ['status' => 'Selesai']);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Feedback berhasil disimpan']);
    }

    public function countByStatus($status)
    {
        $count = $this->ticketModel->where('status', ucfirst($status))->countAllResults();
        return $this->response->setJSON(['count' => $count]);
    }
}
