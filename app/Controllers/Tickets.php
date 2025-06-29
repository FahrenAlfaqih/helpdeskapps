<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\M_Tiket;
use Config\Database;
use Carbon\Carbon;
use Config\Services;

class Tickets extends Controller
{
    protected $ticketModel;
    protected $db;

    protected function sendEmailToUnitTujuan($id_unit_tujuan, $unit_usaha, $ticketData)
    {
        $builder = $this->db->table('pegawai_penempatan pp');
        $builder->select('u.email, u.nama');
        $builder->join('user u', 'u.id_pegawai = pp.id_pegawai');
        $builder->where('pp.id_unit_kerja', $id_unit_tujuan);
        $builder->where('pp.id_unit_usaha', $unit_usaha);
        $emails = $builder->get()->getResultArray();

        $emailService = Services::email();

        foreach ($emails as $user) {
            $emailService->clear();
            $emailService->setTo($user['email']);
            $emailService->setSubject("Tiket Baru Masuk: " . $ticketData['judul']);
            $emailService->setMessage("Halo {$user['nama']},\n\nAda tiket baru yang masuk ke unit Anda dengan judul:\n\n" . $ticketData['judul'] . "\n\nSilakan cek sistem untuk detailnya.");
            $emailService->send();
        }
    }

    protected function sendEmailToRequestor($requestorEmail, $ticketData, $subject, $message)
    {
        $emailService = Services::email();

        $emailService->clear();
        $emailService->setTo($requestorEmail);
        $emailService->setSubject($subject);
        $emailService->setMessage($message);
        $emailService->send();
    }


    public function __construct()
    {
        helper(['form', 'url', 'session']);
        $this->ticketModel = new M_Tiket();
        $this->db = Database::connect();
    }

    public function index()
    {
        return view('tickets/index');
    }

    public function createView()
    {
        $session = session();
        $unitUsaha = $session->get('unit_usaha_id'); // pastikan ini tersedia

        $kategori = $this->db->table('kategori')
            // ->where('unit_usaha', $unitUsaha)
            ->get()
            ->getResultArray();

        $subkategori = $this->db->table('sub_kategori as sk')
            ->join('kategori as k', 'sk.id_kategori = k.id_kategori')
            // ->where('k.unit_usaha', $unitUsaha)
            ->get()
            ->getResultArray();

        $ruangan = $this->db->table('ruangan')
            // ->where('k.unit_usaha', $unitUsaha)
            ->get()
            ->getResultArray();

        $allowedUnitIds = ['E13', 'E21'];

        $units = $this->db->table('unit_kerja')
            ->whereIn('id_unit_kerja', $allowedUnitIds)
            ->get()
            ->getResultArray();

        return view('tickets/create', [
            'units' => $units,
            'kategori' => $kategori,
            'subkategori' => $subkategori,
            'ruangan' => $ruangan,
        ]);
    }

    public function create()
    {
        $session = session();
        $idPegawaiRequestor = $session->get('id_pegawai');

        $penempatan = $this->db->table('pegawai_penempatan as pp')
            ->select('pp.id_unit_level, pp.id_unit_bisnis, pp.id_unit_usaha, pp.id_unit_organisasi, pp.id_unit_kerja, pp.id_unit_kerja_sub, pp.id_unit_lokasi')
            ->where('pp.id_pegawai', $idPegawaiRequestor)
            ->get()->getRow();

        if (!$penempatan) {
            return redirect()->back()->with('error', 'Data penempatan requestor tidak ditemukan.');
        }

        $validation = \Config\Services::validation();
        $rules = [
            'judul' => 'required|max_length[255]',
            'deskripsi' => 'required',
            'id_unit_tujuan' => 'required',
            'kategori' => 'required',
            'subkategori' => 'required',
            'gambar' => 'permit_empty|is_image[gambar]|max_size[gambar,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $fileName = null;
        if ($file = $this->request->getFile('gambar')) {
            if ($file->isValid() && !$file->hasMoved()) {
                $fileName = $file->getRandomName();
                $file->move(WRITEPATH . 'uploads', $fileName);
            }
        }

        $data = [
            'id_tiket' => $this->ticketModel->generateIdTiket(),
            'id_pegawai_requestor' => $idPegawaiRequestor,
            'unit_level_requestor' => $penempatan->id_unit_level ?? null,
            'unit_bisnis_requestor' => $penempatan->id_unit_bisnis ?? null,
            'unit_usaha_requestor' => $penempatan->id_unit_usaha ?? null,
            'unit_organisasi_requestor' => $penempatan->id_unit_organisasi ?? null,
            'unit_kerja_requestor' => $penempatan->id_unit_kerja ?? null,
            'unit_kerja_sub_requestor' => $penempatan->id_unit_kerja_sub ?? null,
            'unit_lokasi_requestor' => $penempatan->id_unit_lokasi ?? null,
            'judul' => $this->request->getPost('judul'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'id_unit_tujuan' => $this->request->getPost('id_unit_tujuan'),
            'id_unit_kerja_sub_tujuan' => $this->request->getPost('id_unit_kerja_sub_tujuan'),
            'kategori_id' => $this->request->getPost('kategori'),
            'subkategori_id' => $this->request->getPost('subkategori'),
            'id_ruangan' => $penempatan->id_unit_kerja_sub,
            'prioritas' => 'Low',
            'komentar_staff' => null,
            'gambar' => $fileName,
            'status' => 'Open',
        ];

        $this->ticketModel->insert($data);
        $this->sendEmailToUnitTujuan($data['id_unit_tujuan'], $data['unit_usaha_requestor'], $data);
        return redirect()->to('/tickets')->with('success', 'Tiket berhasil dibuat dan dikirim ke unit terkait.');
    }

    public function list()
    {
        $request = service('request');
        $session = session();

        $start = (int) ($request->getGet('start') ?? 0);
        $length = (int) ($request->getGet('length') ?? 10);
        $draw = (int) ($request->getGet('draw') ?? 1);
        $searchValue = $request->getGet('search')['value'] ?? '';

        $idPegawai = $session->get('id_pegawai');


        $builder = $this->db->table('tiket t');
        $builder->select('t.id_tiket, t.judul, k.nama_kategori, sk.nama_subkategori, r.nm_ruangan, t.prioritas, t.status, t.created_at, t.confirm_by_requestor');
        $builder->join('kategori k', 't.kategori_id = k.id_kategori', 'left');
        $builder->join('sub_kategori sk', 't.subkategori_id = sk.id_subkategori', 'left');
        $builder->join('ruangan r', 't.id_ruangan = r.id_ruangan', 'left');
        $builder->where('t.id_pegawai_requestor', $idPegawai);

        // Untuk hitung total data sebelum filter search
        $totalData = $builder->countAllResults(false);

        // Jika ada pencarian
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('t.judul', $searchValue)
                ->orLike('t.deskripsi', $searchValue)
                ->orLike('k.nama_kategori', $searchValue)
                ->orLike('sk.nama_subkategori', $searchValue)
                ->groupEnd();
        }

        // Hitung total setelah filter search
        $totalFiltered = $builder->countAllResults(false);

        // Ambil data dengan limit dan offset
        $data = $builder->orderBy('t.created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            "draw" => $draw,
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data" => $data,
        ]);
    }


    public function listForUnit()
    {
        $session = session();
        $idPegawai = $session->get('id_pegawai');

        // Ambil penempatan pegawai (unit usaha dan unit kerja)
        $builder = $this->db->table('pegawai_penempatan as pp');
        $builder->select('pp.id_unit_level, pp.id_unit_bisnis, pp.id_unit_usaha, pp.id_unit_organisasi, pp.id_unit_kerja, pp.id_unit_kerja_sub, pp.id_unit_lokasi');
        $builder->where('pp.id_pegawai', $idPegawai);
        $penempatan = $builder->get()->getRow();

        if (!$penempatan) {
            return $this->response->setJSON(['error' => 'Penempatan pegawai tidak ditemukan']);
        }

        // Buat query tiket dengan join ke user untuk mendapatkan nama assigned pegawai dan requestor
        $builder = $this->db->table('tiket t');
        $builder->select('t.*, u.nama as assigned_nama, ur.nama as requestor_nama');
        $builder->join('user u', 'u.id_pegawai = t.assigned_to', 'left');
        $builder->join('user ur', 'ur.id_pegawai = t.id_pegawai_requestor', 'left'); // Join untuk requestor

        $builder->groupStart();

        // Cek jika user yang login adalah Korporat IT: unit_bisnis B1, unit_usaha C1, unit_kerja E13
        if ($penempatan->id_unit_bisnis === 'B1' && $penempatan->id_unit_usaha === 'C1' && $penempatan->id_unit_kerja === 'E13') {

            // Tiket open/in progress/done yang:
            // - unit_bisnis_requestor = B1 dan unit_usaha_requestor = C1 (korporat) 
            // OR
            // - unit_bisnis_requestor = B3 dan unit_usaha_requestor NOT IN C1,C2,C3,C4,C5 (klinik)
            $builder->groupStart();
            $builder->whereIn('t.status', ['Open', 'In Progress', 'Done']);
            $builder->groupStart();
            // Korporat tiket
            $builder->where('t.unit_bisnis_requestor', 'B1');
            $builder->where('t.unit_usaha_requestor', 'C1');
            $builder->groupEnd();
            $builder->orGroupStart();
            // Klinik tiket (unit_usaha NOT IN C1-C5)
            $builder->where('t.unit_bisnis_requestor', 'B3');
            $builder->whereNotIn('t.unit_usaha_requestor', ['C1', 'C2', 'C3', 'C4', 'C5']);
            $builder->groupEnd();
            $builder->groupEnd();

            $builder->whereIn('t.id_unit_tujuan', ['E13', 'E21']); // tujuan bisa E13 atau E21 untuk korporat IT

        } else {
            // Untuk pegawai lain, tiket sesuai unit kerja dan unit usaha serta bisnis mereka
            $builder->whereIn('t.status', ['Open', 'In Progress', 'Done']);
            $builder->where('t.id_unit_tujuan', $penempatan->id_unit_kerja);
            $builder->where('t.unit_bisnis_requestor', $penempatan->id_unit_bisnis);
            $builder->where('t.unit_usaha_requestor', $penempatan->id_unit_usaha);
        }

        $builder->groupEnd();

        $builder->orGroupStart();
        // Tiket yang sudah closed tapi assigned_to = pegawai ini (yang dikerjakan sendiri)
        $builder->where('t.status', 'Closed');
        $builder->where('t.assigned_to', $idPegawai);
        $builder->groupEnd();

        $totalData = $builder->countAllResults(false);

        $data = $builder->orderBy('t.created_at', 'DESC')->get()->getResultArray();

        // Format tanggal created_at pake Carbon
        foreach ($data as &$ticket) {
            $ticket['created_at'] = \Carbon\Carbon::parse($ticket['created_at'])
                ->locale('id')
                ->isoFormat('D MMMM YYYY HH:mm');
        }

        $response = [
            "draw" => (int) $this->request->getGet('draw'),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalData,
            "data" => $data,
        ];

        return $this->response->setJSON($response);
    }


    public function takeTicket()
    {
        $session = session();
        $idPegawai = $session->get('id_pegawai');
        $idTiket = $this->request->getPost('id_tiket');
        $status = $this->request->getPost('status') ?? 'In Progress'; // ambil status dari inputan
        $komentarPenyelesaian = $this->request->getPost('komentar_penyelesaian') ?? null;
        $prioritas = $this->request->getPost('prioritas') ?? null;
        $komentarStaff = $this->request->getPost('komentar_staff') ?? null;

        $ticket = $this->ticketModel->find($idTiket);
        if (!$ticket) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket tidak ditemukan']);
        }

        if ($ticket['assigned_to'] && $ticket['assigned_to'] != $idPegawai) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket sudah diambil oleh orang lain']);
        }

        // Validasi komentar_staff wajib jika status = Done
        if ($status === 'Done' && (empty($komentarStaff) || trim($komentarStaff) === '')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Komentar staff wajib diisi jika status tiket Done']);
        }

        // Update data tiket
        $updateData = [
            'assigned_to' => $idPegawai,
            'status' => $status, // langsung set status sesuai pilihan dropdown
            'komentar_penyelesaian' => $komentarPenyelesaian,
            'komentar_staff' => $komentarStaff,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (in_array($prioritas, ['High', 'Medium', 'Low'])) {
            $updateData['prioritas'] = $prioritas;
        }

        $this->ticketModel->update($idTiket, $updateData);

        // Ambil data requestor untuk email
        $requestor = $this->db->table('user')
            ->select('email, nama')
            ->where('id_pegawai', $ticket['id_pegawai_requestor'])
            ->get()->getRow();

        if ($requestor) {
            // Tentukan subject dan pesan email berdasarkan status
            if ($status === 'In Progress') {
                $subject = "Tiket Anda Sedang Dalam Proses";
                $message = "Halo {$requestor->nama},\n\nTiket dengan judul \"{$ticket['judul']}\" telah diambil dan sedang dalam proses pengerjaan.";
            } elseif ($status === 'Done') {
                $subject = "Tiket Anda Telah Selesai Dikerjakan";
                $message = "Halo {$requestor->nama},\n\nTiket dengan judul \"{$ticket['judul']}\" telah selesai dikerjakan. Silakan cek dan konfirmasi.";
            }

            // Kirim email sesuai status
            $this->sendEmailToRequestor($requestor->email, $ticket, $subject, $message);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Tiket berhasil diambil dan diperbarui']);
    }


    public function finish()
    {
        $session = session();
        $userId = $session->get('id_pegawai');
        $idTiket = $this->request->getPost('id_tiket');

        $ticket = $this->ticketModel->find($idTiket);

        if (!$ticket) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket tidak ditemukan']);
        }

        if ($ticket['assigned_to'] !== $userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak berhak mengubah status tiket ini']);
        }

        if ($ticket['status'] !== 'In Progress') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Status tiket bukan In Progress']);
        }

        $this->ticketModel->update($idTiket, [
            'status' => 'Done',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $requestor = $this->db->table('user')->select('email, nama')->where('id_pegawai', $ticket['id_pegawai_requestor'])->get()->getRow();

        if ($requestor) {
            $subject = "Tiket Anda Telah Selesai Dikerjakan";
            $message = "Halo {$requestor->nama},\n\nTiket dengan judul \"{$ticket['judul']}\" telah selesai dikerjakan. Silakan cek dan konfirmasi.";
            $this->sendEmailToRequestor($requestor->email, $ticket, $subject, $message);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Status tiket berhasil diubah menjadi Done']);
    }

    public function confirmCompletion()
    {
        $idTiket = $this->request->getPost('id_tiket');
        $statusKonfirmasi = $this->request->getPost('status'); // Closed / Open
        $komentar = $this->request->getPost('komentar_penyelesaian') ?? null;
        $ratingTime = $this->request->getPost('rating_time') ?? null;
        $ratingService = $this->request->getPost('rating_service') ?? null;

        $ticket = $this->ticketModel->find($idTiket);
        if (!$ticket) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket tidak ditemukan']);
        }

        if ($statusKonfirmasi === 'Closed') {
            if ($ticket['status'] !== 'Done') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket belum berstatus Done']);
            }

            if (empty($komentar) || empty($ratingService) || empty($ratingTime)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Komentar, rating service dan rating waktu wajib diisi untuk menyelesaikan tiket']);
            }

            $updateData = [
                'status' => 'Closed',
                'confirm_by_requestor' => 1,
                'komentar_penyelesaian' => $komentar,
                'rating_time' => $ratingTime,
                'rating_service' => $ratingService,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        } elseif ($statusKonfirmasi === 'Open') {
            $updateData = [
                'status' => 'Open',
                'assigned_to' => null,
                // 'prioritas' => null,
                'komentar_staff' => null,
                'komentar_penyelesaian' => $komentar,
                'rating_time' => null,
                'rating_service' => null,
                'confirm_by_requestor' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Status tidak valid']);
        }

        // Lakukan update dan cek apakah berhasil
        $result = $this->ticketModel->update($idTiket, $updateData);

        if (!$result) {
            // Ambil error jika update gagal
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengupdate tiket',
                'debug' => $this->ticketModel->errors()
            ]);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Konfirmasi berhasil disimpan']);
    }



    public function boardStaffView()
    {
        return view('tickets/board_staff');
    }

    public function detail($id)
    {
        $builder = $this->db->table('tiket t');
        $builder->select([
            't.*',
            't.gambar',
            'u_assigned.nama as assigned_nama',
            'p_assigned.telpon1 as assigned_telpon1',
            'p_assigned.telpon2 as assigned_telpon2',
            'k.nama_kategori',
            'sk.nama_subkategori',
            'r.nm_ruangan',
            'req.nama as requestor_nama',
            'req.email as requestor_email',
            'p_requestor.telpon1 as requestor_telpon1',
            'p_requestor.telpon2 as requestor_telpon2',
            'pp.id_unit_level',
            'ul.nm_unit_level',
            'pp.id_unit_bisnis',
            'ub.nm_unit_bisnis',
            'pp.id_unit_usaha',
            'uu.nm_unit_usaha',
            'pp.id_unit_organisasi',
            'uo.nm_unit_organisasi',
            'pp.id_unit_kerja',
            'uk.nm_unit_kerja',
            'pp.id_unit_kerja_sub',
            'uks.nm_unit_kerja_sub',
            'pp.id_unit_lokasi',
            'ulok.nm_unit_lokasi',
        ]);
        $builder->join('user u_assigned', 'u_assigned.id_pegawai = t.assigned_to', 'left');
        $builder->join('pegawai p_assigned', 'p_assigned.id_pegawai = t.assigned_to', 'left');
        $builder->join('kategori k', 't.kategori_id = k.id_kategori', 'left');
        $builder->join('sub_kategori sk', 't.subkategori_id = sk.id_subkategori', 'left');
        $builder->join('ruangan r', 't.id_ruangan = r.id_ruangan', 'left');
        $builder->join('user req', 'req.id_pegawai = t.id_pegawai_requestor', 'left');
        $builder->join('pegawai p_requestor', 'p_requestor.id_pegawai = t.id_pegawai_requestor', 'left');
        $builder->join('pegawai_penempatan pp', 'pp.id_pegawai = t.id_pegawai_requestor', 'left');
        $builder->join('unit_level ul', 'pp.id_unit_level = ul.id_unit_level', 'left');
        $builder->join('unit_bisnis ub', 'pp.id_unit_bisnis = ub.id_unit_bisnis', 'left');
        $builder->join('unit_usaha uu', 'pp.id_unit_usaha = uu.id_unit_usaha', 'left');
        $builder->join('unit_organisasi uo', 'pp.id_unit_organisasi = uo.id_unit_organisasi', 'left');
        $builder->join('unit_kerja uk', 'pp.id_unit_kerja = uk.id_unit_kerja', 'left');
        $builder->join('unit_kerja_sub uks', 'pp.id_unit_kerja_sub = uks.id_unit_kerja_sub', 'left');
        $builder->join('unit_lokasi ulok', 'pp.id_unit_lokasi = ulok.id_unit_lokasi', 'left');

        $builder->where('t.id_tiket', $id);
        $ticket = $builder->get()->getRowArray();

        if (!$ticket) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket tidak ditemukan']);
        }

        // Format created_at dan updated_at untuk waktu yang lebih mudah dibaca
        $createdAt = Carbon::parse($ticket['created_at'])->locale('id')->isoFormat('D MMMM YYYY, HH:mm');
        $updatedAt = Carbon::parse($ticket['updated_at'])->locale('id')->isoFormat('D MMMM YYYY, HH:mm');

        $data = [
            'id_tiket' => $ticket['id_tiket'],
            'judul' => $ticket['judul'],
            'gambar' => $ticket['gambar'],
            'deskripsi' => $ticket['deskripsi'],
            'prioritas' => $ticket['prioritas'],
            'status' => $ticket['status'],
            'requestor_nama' => $ticket['requestor_nama'] ?? '-',
            'requestor_email' => $ticket['requestor_email'] ?? '-',
            'requestor_telpon1' => $ticket['requestor_telpon1'] ?? '-',
            'requestor_telpon2' => $ticket['requestor_telpon2'] ?? '-',
            'assigned_nama' => $ticket['assigned_nama'] ?? '-',
            'assigned_telpon1' => $ticket['assigned_telpon1'] ?? '-',
            'assigned_telpon2' => $ticket['assigned_telpon2'] ?? '-',
            'nm_ruangan' => $ticket['nm_ruangan'] ?? '-',
            'kategori' => $ticket['nama_kategori'] ?? '-',
            'subkategori' => $ticket['nama_subkategori'] ?? '-',
            'komentar_penyelesaian' => $ticket['komentar_penyelesaian'] ?? '-',
            'komentar_staff' => $ticket['komentar_staff'] ?? '-',
            'rating_time' => $ticket['rating_time'] ?? '-',
            'rating_service' => $ticket['rating_service'] ?? '-',

            'req_penempatan' => [
                'unit_level' => $ticket['nm_unit_level'] ?? $ticket['id_unit_level'] ?? '-',
                'unit_bisnis' => $ticket['nm_unit_bisnis'] ?? $ticket['id_unit_bisnis'] ?? '-',
                'unit_usaha' => $ticket['nm_unit_usaha'] ?? $ticket['id_unit_usaha'] ?? '-',
                'unit_organisasi' => $ticket['nm_unit_organisasi'] ?? $ticket['id_unit_organisasi'] ?? '-',
                'unit_kerja' => $ticket['nm_unit_kerja'] ?? $ticket['id_unit_kerja'] ?? '-',
                'unit_kerja_sub' => $ticket['nm_unit_kerja_sub'] ?? $ticket['id_unit_kerja_sub'] ?? '-',
                'unit_lokasi' => $ticket['nm_unit_lokasi'] ?? $ticket['id_unit_lokasi'] ?? '-',
            ],

            'created_at' => $createdAt,
            'updated_at' => $updatedAt, // Add updated_at for the completion time
        ];

        return $this->response->setJSON(['status' => 'success', 'data' => $data]);
    }
}
