<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\M_Tiket;
use Config\Database;
use Carbon\Carbon;

class Tickets extends Controller
{
    protected $ticketModel;
    protected $db;

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

        // Ambil kategori berdasar unit usaha requestor
        $kategori = $this->db->table('kategori')
            ->where('unit_usaha', $unitUsaha)
            ->get()
            ->getResultArray();

        // Ambil subkategori berdasar kategori yg ada di unit usaha itu
        // Bisa query join atau ambil per kategori nanti di frontend
        $subkategori = $this->db->table('sub_kategori as sk')
            ->join('kategori as k', 'sk.id_kategori = k.id_kategori')
            ->where('k.unit_usaha', $unitUsaha)
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
        ]);
    }




    // Simpan tiket baru, simpan juga data penempatan requestor dan unit tujuan tiket
    public function create()
    {
        $session = session();
        $idPegawaiRequestor = $session->get('id_pegawai');

        // Ambil penempatan lengkap requestor
        $penempatan = $this->db->table('pegawai_penempatan as pp')
            ->select('pp.id_unit_level, pp.id_unit_bisnis, pp.id_unit_usaha, pp.id_unit_organisasi, pp.id_unit_kerja, pp.id_unit_kerja_sub, pp.id_unit_lokasi')
            ->where('pp.id_pegawai', $idPegawaiRequestor)
            ->get()->getRow();

        if (!$penempatan) {
            return redirect()->back()->with('error', 'Data penempatan requestor tidak ditemukan.');
        }

        // Validasi
        $validation = \Config\Services::validation();
        $rules = [
            'judul' => 'required|max_length[255]',
            'deskripsi' => 'required',
            'prioritas' => 'required|in_list[High,Medium,Low]',
            'id_unit_tujuan' => 'required',
            'kategori' => 'required',
            'subkategori' => 'required',
            'gambar' => 'permit_empty|is_image[gambar]|max_size[gambar,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Upload gambar jika ada
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
            'kategori_id' => $this->request->getPost('kategori'),
            'subkategori_id' => $this->request->getPost('subkategori'),
            'prioritas' => $this->request->getPost('prioritas'),
            'gambar' => $fileName,
            'status' => 'Open',
        ];

        $this->ticketModel->insert($data);

        // $this->ticketModel->insert([
        //     'id_pegawai_requestor' => $idPegawaiRequestor,
        //     'unit_level_requestor' => $penempatan->id_unit_level ?? null,
        //     'unit_bisnis_requestor' => $penempatan->id_unit_bisnis ?? null,
        //     'unit_usaha_requestor' => $penempatan->id_unit_usaha ?? null,
        //     'unit_organisasi_requestor' => $penempatan->id_unit_organisasi ?? null,
        //     'unit_kerja_requestor' => $penempatan->id_unit_kerja ?? null,
        //     'unit_kerja_sub_requestor' => $penempatan->id_unit_kerja_sub ?? null,
        //     'unit_lokasi_requestor' => $penempatan->id_unit_lokasi ?? null,
        //     'judul' => $this->request->getPost('judul'),
        //     'deskripsi' => $this->request->getPost('deskripsi'),
        //     'id_unit_tujuan' => $this->request->getPost('id_unit_tujuan'),
        //     'kategori_id' => $this->request->getPost('kategori'),
        //     'subkategori_id' => $this->request->getPost('subkategori'),
        //     'prioritas' => $this->request->getPost('prioritas'),
        //     'gambar' => $fileName,
        //     'status' => 'Open',
        // ]);

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

        // Query builder dengan join kategori & subkategori
        $builder = $this->db->table('tiket t');
        $builder->select('t.id_tiket, t.judul, k.nama_kategori, sk.nama_subkategori, t.prioritas, t.status, t.created_at, t.confirm_by_requestor');
        $builder->join('kategori k', 't.kategori_id = k.id_kategori', 'left');
        $builder->join('sub_kategori sk', 't.subkategori_id = sk.id_subkategori', 'left');
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


    // public function listForUnit()
    // {
    //     $session = session();
    //     $idPegawai = $session->get('id_pegawai');

    //     // Ambil penempatan pegawai (unit usaha dan unit kerja)
    //     $builder = $this->db->table('pegawai_penempatan as pp');
    //     $builder->select('pp.id_unit_level, pp.id_unit_bisnis, pp.id_unit_usaha, pp.id_unit_organisasi, pp.id_unit_kerja, pp.id_unit_kerja_sub, pp.id_unit_lokasi');
    //     $builder->where('pp.id_pegawai', $idPegawai);
    //     $penempatan = $builder->get()->getRow();


    //     if (!$penempatan) {
    //         return $this->response->setJSON(['error' => 'Penempatan pegawai tidak ditemukan']);
    //     }

    //     // Buat query tiket dengan join ke user untuk mendapatkan nama assigned pegawai
    //     $builder = $this->db->table('tiket t');
    //     $builder->select('t.*, u.nama as assigned_nama, ur.nama as requestor_nama');
    //     $builder->join('user u', 'u.id_pegawai = t.assigned_to', 'left');
    //     $builder->join('user ur', 'ur.id_pegawai = t.id_pegawai_requestor', 'left'); // Join untuk requestor

    //     $builder->groupStart();
    //     // Tiket dengan status open dan in progress di unit kerja dan unit usaha yang sama
    //     $builder->whereIn('t.status', ['Open', 'In Progress', 'Done']);
    //     $builder->where('t.id_unit_tujuan', $penempatan->id_unit_kerja);
    //     $builder->where('t.unit_bisnis_requestor', $penempatan->id_unit_bisnis);
    //     $builder->where('t.unit_usaha_requestor', $penempatan->id_unit_usaha);
    //     $builder->groupEnd();

    //     $builder->orGroupStart();
    //     // Tiket yang sudah closed tapi assigned_to = pegawai ini (yang dikerjakan sendiri)
    //     $builder->where('t.status', 'Closed');
    //     $builder->where('t.assigned_to', $idPegawai);
    //     $builder->groupEnd();

    //     $totalData = $builder->countAllResults(false);

    //     $data = $builder->orderBy('t.created_at', 'DESC')->get()->getResultArray();

    //     // Format tanggal created_at pake Carbon
    //     foreach ($data as &$ticket) {
    //         $ticket['created_at'] = Carbon::parse($ticket['created_at'])
    //             ->locale('id')
    //             ->isoFormat('D MMMM YYYY HH:mm');
    //     }

    //     $response = [
    //         "draw" => (int) $this->request->getGet('draw'),
    //         "recordsTotal" => $totalData,
    //         "recordsFiltered" => $totalData,
    //         "data" => $data,
    //     ];

    //     return $this->response->setJSON($response);
    // }


    // Staff/kepala unit ambil tiket dan update status serta komentar penyelesaian
    public function takeTicket()
    {
        $session = session();
        $idPegawai = $session->get('id_pegawai');
        $idTiket = $this->request->getPost('id_tiket');
        $status = $this->request->getPost('status') ?? 'In Progress';
        $komentar = $this->request->getPost('komentar_penyelesaian') ?? null;

        $ticket = $this->ticketModel->find($idTiket);
        if (!$ticket) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket tidak ditemukan']);
        }

        if ($ticket['assigned_to'] && $ticket['assigned_to'] != $idPegawai) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket sudah diambil oleh orang lain']);
        }

        $this->ticketModel->update($idTiket, [
            'assigned_to' => $idPegawai,
            'status' => $status,
            'komentar_penyelesaian' => $komentar,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

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

        return $this->response->setJSON(['status' => 'success', 'message' => 'Status tiket berhasil diubah menjadi Done']);
    }


    public function confirmCompletion()
    {
        $idTiket = $this->request->getPost('id_tiket');
        $konfirmasi = $this->request->getPost('confirm_by_requestor') ?? 0;
        $ratingTime = $this->request->getPost('rating_time') ?? null;
        $ratingService = $this->request->getPost('rating_service') ?? null;
        $komentar = $this->request->getPost('komentar_penyelesaian') ?? null;

        $ticket = $this->ticketModel->find($idTiket);
        if (!$ticket) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket tidak ditemukan']);
        }

        if ($ticket['status'] !== 'Done') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket belum berstatus Done']);
        }

        // Update data tiket
        $this->ticketModel->update($idTiket, [
            'confirm_by_requestor' => 1,
            'rating_time' => $ratingTime,
            'rating_service' => $ratingService,
            'komentar_penyelesaian' => $komentar,
            'status' => 'Closed',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Konfirmasi berhasil disimpan']);
    }


    public function boardStaffView()
    {
        return view('tickets/board_staff');
    }

    public function detail($id)
    {
        $ticket = $this->ticketModel
            ->select('tiket.*, user.nama as assigned_nama, k.nama_kategori, sk.nama_subkategori')
            ->join('user', 'user.id_pegawai = tiket.assigned_to', 'left')
            ->join('kategori k', 'tiket.kategori_id = k.id_kategori', 'left')
            ->join('sub_kategori sk', 'tiket.subkategori_id = sk.id_subkategori', 'left')
            ->where('id_tiket', $id)
            ->first();

        if (!$ticket) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tiket tidak ditemukan']);
        }

        // Mengambil data requestor
        $requestor = $this->db->table('user')->select('nama, email')->where('id_pegawai', $ticket['id_pegawai_requestor'])->get()->getRow();

        // Menggunakan Carbon untuk format tanggal
        $createdAt = Carbon::parse($ticket['created_at'])->locale('id')->isoFormat('D MMMM YYYY, HH:mm');

        $data = [
            'id_tiket' => $ticket['id_tiket'],
            'judul' => $ticket['judul'],
            'deskripsi' => $ticket['deskripsi'],
            'prioritas' => $ticket['prioritas'],
            'status' => $ticket['status'],
            'requestor_nama' => $requestor ? $requestor->nama : '-',
            'requestor_email' => $requestor ? $requestor->email : '-',
            'assigned_nama' => $ticket['assigned_nama'] ?? '-',
            'gambar' => $ticket['gambar'],
            'created_at' => $createdAt,  // Format tanggal
            'komentar_penyelesaian' => $ticket['komentar_penyelesaian'] ?? '-',
            'rating_time' => $ticket['rating_time'] ?? '-',
            'rating_service' => $ticket['rating_service'] ?? '-',
            'nama_kategori' => $ticket['nama_kategori'] ?? '-',
            'nama_subkategori' => $ticket['nama_subkategori'] ?? '-',
        ];

        return $this->response->setJSON(['status' => 'success', 'data' => $data]);
    }
}
