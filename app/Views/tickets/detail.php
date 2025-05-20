<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-3xl font-bold mb-6 text-blue-900 border-b pb-4">Detail Tiket</h1>

    <div id="ticketDetail" class="space-y-6 text-gray-700">
        <p class="text-center text-blue-600 font-semibold">Memuat data tiket...</p>
    </div>

    <div id="actionButtons" class="mt-6 flex space-x-4"></div>

    <button id="openUpdateBtn"
        class="mt-6 bg-blue-600 text-white px-6 py-2 rounded-lg
         shadow-md hover:bg-blue-700 hover:shadow-lg
         transition duration-300 ease-in-out
         disabled:opacity-50 disabled:cursor-not-allowed
         focus:outline-none focus:ring-2 focus:ring-blue-400
         active:scale-95"
        disabled>Update Status</button>

</div>

<!-- Modal Update Status -->
<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded shadow-lg max-w-md w-full p-6">
        <h2 class="text-2xl font-semibold mb-4 text-blue-900">Update Status Tiket</h2>
        <form id="updateForm" class="space-y-4">
            <input type="hidden" id="modal_ticket_id" />
            <select id="modal_status" class="border border-blue-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                <option value="">Pilih Status</option>
                <option value="Menunggu">Menunggu</option>
                <option value="Open">Open</option>
                <option value="In Progress">In Progress</option>
                <option value="Done">Done</option>
                <option value="Selesai">Selesai</option>
                <option value="Belum Selesai">Belum Selesai</option>
            </select>
            <div class="flex justify-end space-x-3">
                <button type="button" id="cancelBtn" class="px-4 py-2 rounded border border-blue-400 hover:bg-blue-100">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal Assign Staff -->
<div id="assignStaffModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded shadow-lg max-w-lg w-full p-6">
        <h2 class="text-xl font-semibold mb-4 text-blue-900">Atur Penanggung Jawab Tiket</h2>
        <form id="assignStaffForm" class="space-y-4">
            <input type="hidden" id="assign_ticket_id" />
            <p><strong>Judul Tiket:</strong> <span id="assign_ticket_title"></span></p>
            <p><strong>Kategori:</strong> <span id="assign_ticket_kategori"></span></p>
            <p><strong>Tanggal Dibuat:</strong> <span id="assign_ticket_tanggal"></span></p>
            <p><strong>Ruangan:</strong> <span id="assign_ticket_ruangan"></span></p>
            <p><strong>Permasalahan:</strong> <span id="assign_ticket_description"></span></p>

            <label for="assigned_staff" class="block font-medium text-blue-700">Pilih Pegawai</label>
            <select id="assigned_staff" class="border border-blue-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                <option value="">-- Pilih Pegawai --</option>
            </select>

            <label for="deadline" class="block font-medium text-blue-700">Batas Waktu (Deadline)</label>
            <input type="date" id="deadline" class="border border-blue-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-400" required />

            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" id="cancelAssignBtn" class="px-4 py-2 rounded border border-blue-400 hover:bg-blue-100">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Feedback -->
<div id="feedbackModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded shadow-lg max-w-md w-full p-6">
        <h2 class="text-2xl font-semibold mb-4 text-blue-900" id="feedbackTitle">Feedback Tiket</h2>
        <form id="feedbackForm" class="space-y-4">
            <input type="hidden" id="feedback_ticket_id" />
            <label for="feedback_text" class="block font-medium">Tulis feedback / komentar:</label>
            <textarea id="feedback_text" rows="4" class="border border-blue-300 rounded px-3 py-2 w-full" required></textarea>
            <label for="feedback_rating" class="block font-medium mt-2">Rating:</label>
            <select id="feedback_rating" class="border border-blue-300 rounded px-3 py-2 w-full" required>
                <option value="">-- Pilih Rating --</option>
                <option value="1">1 - Buruk</option>
                <option value="2">2 - Kurang</option>
                <option value="3">3 - Cukup</option>
                <option value="4">4 - Baik</option>
                <option value="5">5 - Sangat Baik</option>
            </select>

            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" id="cancelFeedbackBtn" class="px-4 py-2 rounded border border-blue-400 hover:bg-blue-100">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Kirim</button>
            </div>
        </form>
    </div>
</div>


<script>
    const ticketId = <?= json_encode($ticketId) ?>;
    const ticketDetailEl = document.getElementById('ticketDetail');
    const openUpdateBtn = document.getElementById('openUpdateBtn');
    const updateModal = document.getElementById('updateModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const cancelFeedbackBtn = document.getElementById('cancelFeedbackBtn');
    const updateForm = document.getElementById('updateForm');
    const modalTicketId = document.getElementById('modal_ticket_id');
    const modalStatus = document.getElementById('modal_status');
    const actionButtons = document.getElementById('actionButtons');

    async function loadTicketDetail() {
        try {
            ticketDetailEl.innerHTML = '<p class="text-center text-blue-600 font-semibold mt-6">Memuat data tiket...</p>';

            const res = await fetch(`/tickets/api-detail/${ticketId}`);
            if (!res.ok) throw new Error('Gagal memuat data tiket, status: ' + res.status);

            const ticket = await res.json();

            // Helper untuk format tanggal dan escape HTML
            function formatDate(dateStr) {
                return dateStr ? new Date(dateStr).toLocaleDateString() : '-';
            }

            function escapeHtml(text) {
                if (!text) return '-';
                return text.replace(/[&<>"']/g, (m) => {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    } [m];
                });
            }

            ticketDetailEl.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-7xl mx-auto bg-white p-6 rounded-lg shadow">
                <div>
                    <h3 class="text-sm font-semibold text-blue-700 mb-1">Judul</h3>
                    <p class="text-gray-900">${escapeHtml(ticket.title)}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-blue-700 mb-1">Status</h3>
                    <p class="font-semibold text-indigo-700">${escapeHtml(ticket.status)}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-blue-700 mb-1">Deadline</h3>
                    <p>${formatDate(ticket.deadline)}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-blue-700 mb-1">Kategori</h3>
                    <p>${escapeHtml(ticket.kategori)}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-blue-700 mb-1">Ruangan</h3>
                    <p>${escapeHtml(ticket.ruangan_nama)}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-blue-700 mb-1">Jenis Perangkat</h3>
                    <p>${escapeHtml(ticket.jenis_perangkat_nama)}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-blue-700 mb-1">Requestor</h3>
                    <p>${escapeHtml(ticket.requestor_name)}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-blue-700 mb-1">Penanggung Jawab</h3>
                    <p>${escapeHtml(ticket.staff_name || '-')}</p>
                </div>
                <div class="md:col-span-2">
                    <h3 class="text-sm font-semibold text-blue-700 mb-1">Tanggal Dibuat</h3>
                    <p>${new Date(ticket.created_at).toLocaleString()}</p>
                </div>
                <div class="md:col-span-2">
                    <h3 class="text-sm font-semibold text-blue-700 mb-1">Deskripsi</h3>
                    <p class="whitespace-pre-wrap text-gray-800">${escapeHtml(ticket.description)}</p>
                </div>
            </div>
        `;

            modalTicketId.value = ticket.id;
            modalStatus.value = ticket.status;

            setupActionButtons(ticket);
            openUpdateBtn.disabled = false;

        } catch (err) {
            ticketDetailEl.innerHTML = `<p class="text-center text-red-600 font-semibold mt-6">${err.message}</p>`;
            openUpdateBtn.disabled = true;
            console.error('Error loadTicketDetail:', err);
        }
    }



    function setupActionButtons(ticket) {
        const roleId = <?= session()->get('role_id') ?? 0 ?>;
        actionButtons.innerHTML = ''; // reset

        // Kepala unit (2 or 3) dan status Menunggu => tombol Atur Pegawai
        if ((roleId === 2 || roleId === 3) && ticket.status.toLowerCase() === 'menunggu') {
            const btnAssign = document.createElement('button');
            btnAssign.textContent = 'Atur Pegawai';
            btnAssign.className = `
  bg-green-600 text-white px-5 py-2 rounded-lg
  shadow-md hover:bg-green-700 hover:shadow-lg
  transition duration-300 ease-in-out
  focus:outline-none focus:ring-2 focus:ring-green-400
  active:scale-95
  cursor-pointer
`;
            btnAssign.onclick = () => openAssignModal(ticket.id);
            actionButtons.appendChild(btnAssign);
        }

        // Pegawai (4 or 5) dan status Open => tombol Ambil Tiket
        // if ((roleId === 4 || roleId === 5) && ticket.status.toLowerCase() === 'open') {
        //     const btnTake = document.createElement('button');
        //     btnTake.textContent = 'Ambil Tiket';
        //     btnTake.className = 'bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition';
        //     btnTake.onclick = () => takeTicket(ticket.id);
        //     actionButtons.appendChild(btnTake);
        // }
    }

    // Escape HTML untuk keamanan
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Modal Update Status event listeners
    openUpdateBtn.addEventListener('click', () => {
        updateModal.classList.remove('hidden');
    });
    // Cancel buttons
    cancelBtn.addEventListener('click', () => {
        updateModal.classList.add('hidden');
    });
    cancelFeedbackBtn.addEventListener('click', () => {
        feedbackModal.classList.add('hidden');
        updateModal.classList.remove('hidden');
    });
    updateForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const newStatus = modalStatus.value;
        const ticketIdVal = modalTicketId.value;

        if (!newStatus) {
            alert('Pilih status dulu ya');
            return;
        }

        // Kalau status Selesai atau Belum Selesai, buka modal feedback
        if (newStatus === 'Selesai' || newStatus === 'Belum Selesai') {
            updateModal.classList.add('hidden');
            feedbackModal.classList.remove('hidden');
            document.getElementById('feedback_ticket_id').value = ticketIdVal;
            feedbackText.value = '';
            feedbackRating.value = '';
            document.getElementById('feedbackTitle').textContent = (newStatus === 'Selesai') ? 'Feedback Tiket Selesai' : 'Komentar Tiket Belum Selesai';
            feedbackForm.setAttribute('data-status', newStatus);
            return;
        }

        // Kalau status lain, langsung update status via API
        try {
            const res = await fetch('/tickets/update-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ticket_id: ticketIdVal,
                    status: newStatus
                })
            });
            const data = await res.json();

            alert(data.message);
            if (data.status === 'success') {
                updateModal.classList.add('hidden');
                loadTicketDetail(); // fungsi mu yang load detail tiket
            }
        } catch {
            alert('Gagal update status');
        }
    });

    // Modal Assign Staff
    const assignStaffModal = document.getElementById('assignStaffModal');
    const assignStaffForm = document.getElementById('assignStaffForm');
    const cancelAssignBtn = document.getElementById('cancelAssignBtn');

    cancelAssignBtn.addEventListener('click', () => {
        assignStaffModal.classList.add('hidden');
    });

    assignStaffForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const ticketId = document.getElementById('assign_ticket_id').value;
        const assignedStaffId = document.getElementById('assigned_staff').value;
        const deadline = document.getElementById('deadline').value;

        if (!assignedStaffId || !deadline) {
            alert('Pilih pegawai dan isi batas waktu.');
            return;
        }

        try {
            const res = await fetch('/tickets/assign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ticket_id: ticketId,
                    assigned_staff_id: assignedStaffId,
                    deadline: deadline
                })
            });
            const data = await res.json();
            alert(data.message);
            if (data.status === 'success') {
                assignStaffModal.classList.add('hidden');
                loadTicketDetail();
            }
        } catch (error) {
            alert('Gagal menugaskan pegawai.');
        }
    });

    async function openAssignModal(ticketId) {
        const res = await fetch(`/tickets/api-detail/${ticketId}`);
        if (!res.ok) return alert('Gagal memuat data tiket');
        const ticket = await res.json();

        document.getElementById('assign_ticket_id').value = ticket.id;
        document.getElementById('assign_ticket_title').textContent = ticket.title;
        document.getElementById('assign_ticket_kategori').textContent = ticket.kategori || '-';
        document.getElementById('assign_ticket_tanggal').textContent = new Date(ticket.created_at).toLocaleDateString();
        document.getElementById('assign_ticket_ruangan').textContent = ticket.ruangan_nama || '-';
        document.getElementById('assign_ticket_description').textContent = ticket.description || '-';

        // Ambil staff dari unit tujuan tiket (misal IT atau GA)
        const unit = ticket.assigned_unit;
        const staffRes = await fetch(`/tickets/staff/unit/${unit}`);
        if (!staffRes.ok) return alert('Gagal memuat daftar pegawai');
        const staffList = await staffRes.json();

        const staffSelect = document.getElementById('assigned_staff');
        staffSelect.innerHTML = '<option value="">-- Pilih Pegawai --</option>';
        staffList.forEach(staff => {
            const opt = document.createElement('option');
            opt.value = staff.id;
            opt.textContent = staff.full_name;
            staffSelect.appendChild(opt);
        });

        document.getElementById('deadline').value = '';
        assignStaffModal.classList.remove('hidden');
    }


    const feedbackModal = document.getElementById('feedbackModal');
    const feedbackForm = document.getElementById('feedbackForm');
    const feedbackText = document.getElementById('feedback_text');

    updateForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const newStatus = modalStatus.value;
        if (!newStatus) {
            alert('Pilih status dulu ya');
            return;
        }
        const ticketIdVal = modalTicketId.value;

        // Kalau status Selesai atau Belum Selesai tampilkan modal feedback dulu
        if (newStatus === 'Selesai' || newStatus === 'Belum Selesai') {
            updateModal.classList.add('hidden');
            feedbackModal.classList.remove('hidden');
            document.getElementById('feedback_ticket_id').value = ticketIdVal;
            feedbackText.value = '';
            document.getElementById('feedbackTitle').textContent = (newStatus === 'Selesai') ? 'Feedback Tiket Selesai' : 'Komentar Tiket Belum Selesai';
            feedbackForm.setAttribute('data-status', newStatus);
            return;
        }

        // Update status biasa
        try {
            const res = await fetch('/tickets/update-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ticket_id: ticketIdVal,
                    status: newStatus
                })
            });
            const data = await res.json();
            alert(data.message);
            if (data.status === 'success') {
                updateModal.classList.add('hidden');
                loadTicketDetail();
            }
        } catch {
            alert('Gagal update status');
        }
    });

    cancelFeedbackBtn.addEventListener('click', () => {
        feedbackModal.classList.add('hidden');
        updateModal.classList.remove('hidden');
    });

    feedbackForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const ticketId = document.getElementById('feedback_ticket_id').value;
        const status = feedbackForm.getAttribute('data-status');
        const comment = feedbackText.value.trim();
        const rating = feedbackRating.value;

        if (!comment) {
            alert('Tulis feedback atau komentar terlebih dahulu');
            return;
        }

        if (status === 'Selesai' && !rating) {
            alert('Pilih rating dulu ya');
            return;
        }

        let body = {
            ticket_id: ticketId,
            status: status
        };

        if (status === 'Selesai') {
            body.rating = rating;
            body.suggestion = comment;
        } else if (status === 'Belum Selesai') {
            body.comment = comment; // sesuaikan key dengan controller saveComment
        }

        const apiUrl = (status === 'Selesai') ? '/tickets/saveFeedback' : '/tickets/saveComment';

        try {
            const res = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(body)
            });

            const data = await res.json();

            alert(data.message);

            if (data.status === 'success') {
                feedbackModal.classList.add('hidden');
                loadTicketDetail();
            }
        } catch {
            alert('Gagal mengirim feedback/komentar');
        }
    });



    loadTicketDetail();
</script>

<?= $this->endSection() ?>