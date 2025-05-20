<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold mb-6 text-blue-700">Tiket Saya - Pegawai <?= ($roleId == 4) ? 'IT' : 'GA' ?></h1>

    <!-- Tombol Status -->
    <div id="statusButtons" class="flex space-x-3 mb-6">
        <?php
        $statuses = [
            // 'menunggu' => 'Menunggu',
            'open' => 'Open',
            'inprogress' => 'In Progress',
            'done' => 'Done',
            'transfer' => 'Transfer'
        ];
        foreach ($statuses as $key => $label): ?>
            <button
                data-status="<?= esc($key) ?>"
                class="relative px-4 py-2 rounded-lg font-semibold transition
               hover:bg-blue-600 hover:text-white
               focus:outline-none focus:ring-2 focus:ring-blue-500
               <?= ($key === 'menunggu') ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700' ?>">
                <?= esc($label) ?>
                <span
                    id="count_<?= esc($key) ?>"
                    class="absolute -top-1 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                    0
                </span>
            </button>
        <?php endforeach; ?>
    </div>


    <!-- Container tiket -->
    <div id="ticketContainer" class="bg-white rounded-lg shadow p-6 min-h-[300px]">
        <input
            type="search"
            id="searchInput"
            placeholder="Cari tiket..."
            class="mb-6 p-3 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 w-full text-sm font-medium" />

        <div id="ticketList" class="grid grid-cols-1 md:grid-cols-3 gap-6 max-h-[500px] overflow-y-auto">
            <p class="text-center text-blue-600 font-semibold mt-6 col-span-full">Memuat tiket...</p>
        </div>
    </div>

    <!-- Modal Alihkan Tiket -->
    <div id="transferModal" class="hidden fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
        <div class="bg-white rounded p-6 w-96 max-w-full">
            <h2 class="text-xl font-semibold mb-4 text-blue-900">Alihkan Tiket</h2>
            <form id="transferForm" class="space-y-4">
                <input type="hidden" id="transfer_ticket_id" />
                <label for="to_staff" class="block font-medium">Pilih Pegawai</label>
                <select id="to_staff" class="border border-blue-300 rounded px-3 py-2 w-full"></select>
                <label for="reason" class="block font-medium">Alasan Alihkan</label>
                <textarea id="reason" rows="3" class="border border-blue-300 rounded px-3 py-2 w-full"></textarea>
                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" onclick="closeTransferModal()" class="px-4 py-2 border rounded hover:bg-gray-100">Batal</button>
                    <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const userId = <?= json_encode($userId); ?>;
    const statusButtons = document.querySelectorAll('#statusButtons button');
    const ticketListEl = document.getElementById('ticketList');
    const searchInput = document.getElementById('searchInput');

    let currentStatus = 'menunggu'; // default aktif
    let currentKeyword = '';

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

    async function fetchTickets(status, keyword = '') {
        ticketListEl.innerHTML = '<p class="text-center text-blue-600 font-semibold mt-6 col-span-full">Memuat tiket...</p>';

        try {
            const res = await fetch(`/tickets/staff/list/${status}`);
            if (!res.ok) throw new Error('Gagal memuat tiket');
            let tickets = await res.json();

            if (keyword) {
                tickets = tickets.filter(t => t.title.toLowerCase().includes(keyword.toLowerCase()));
            }

            if (tickets.length === 0) {
                ticketListEl.innerHTML = '<p class="text-center text-blue-600 font-semibold mt-6 col-span-full">Tidak ada tiket ditemukan.</p>';
                return;
            }

            ticketListEl.innerHTML = '';
            tickets.forEach(ticket => {
                const card = document.createElement('div');
                card.className = 'border border-blue-300 rounded-2xl p-4 shadow hover:shadow-lg cursor-pointer transition flex flex-col justify-between';

                let innerHTML = `
          <div>
            <h3 class="font-semibold text-blue-900 mb-2 text-lg">${escapeHtml(ticket.title)}</h3>
            <p class="text-sm text-gray-600 mb-1">${new Date(ticket.created_at).toLocaleDateString()}</p>
            <p class="text-sm mb-1"><strong>Kategori:</strong> ${escapeHtml(ticket.kategori || '-')}</p>
            <p class="text-sm mb-1"><strong>Ruangan:</strong> ${escapeHtml(ticket.ruangan_name || '-')}</p>
          </div>
          <span class="self-end inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wide select-none">
            ${escapeHtml(ticket.status)}
          </span>
        `;

                if (status === 'transfer') {
                    innerHTML += `
            <p class="text-sm text-yellow-600 font-semibold mt-2">Alasan Transfer: ${escapeHtml(ticket.transfer_reason)}</p>
            <p class="text-sm"><strong>Deadline:</strong> ${ticket.deadline ? new Date(ticket.deadline).toLocaleDateString() : '-'}</p>
          `;

                    if (ticket.to_staff_id === userId) {
                        innerHTML += `
              <button onclick="acceptTransfer(${ticket.transfer_id})" class="bg-green-500 text-white px-3 py-1 rounded mr-2 hover:bg-green-600">Terima</button>
              <button onclick="rejectTransfer(${ticket.transfer_id})" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Tolak</button>
            `;
                    }
                } else if (status === 'open') {
                    innerHTML += `
            <button onclick="event.stopPropagation(); takeTicket(${ticket.id})" class="bg-green-500 text-white px-4 py-2 rounded mr-2 hover:bg-green-600">Ambil Tiket</button>
            <button onclick="event.stopPropagation(); openTransferModal(${ticket.id}, '${ticket.assigned_unit}')" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Alihkan Tiket</button>
          `;
                }

                card.innerHTML = innerHTML;

                card.onclick = () => {
                    if (status !== 'transfer') {
                        window.location.href = '/tickets/detail/' + ticket.id;
                    }
                };

                ticketListEl.appendChild(card);
            });
        } catch (err) {
            ticketListEl.innerHTML = `<p class="text-center text-red-600 font-semibold mt-6 col-span-full">${err.message}</p>`;
        }
    }


    async function fetchTicketCount(status) {
        try {
            const res = await fetch(`/tickets/staff/count/${status}`);
            if (!res.ok) throw new Error('Gagal memuat jumlah tiket');
            const data = await res.json(); // pastikan API balikin { count: number }
            document.getElementById(`count_${status}`).textContent = `${data.count}`;
        } catch (err) {
            console.error(`Error fetch count for ${status}:`, err);
        }
    }

    statusButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.dataset.status === currentStatus) return;

            currentStatus = btn.dataset.status;

            statusButtons.forEach(b => {
                b.classList.remove('bg-blue-600', 'text-white');
                b.classList.add('bg-blue-100', 'text-blue-700');
            });
            btn.classList.add('bg-blue-600', 'text-white');
            btn.classList.remove('bg-blue-100', 'text-blue-700');

            fetchTickets(currentStatus, currentKeyword);
            searchInput.value = '';
        });
    });

    searchInput.addEventListener('input', e => {
        currentKeyword = e.target.value.trim();
        fetchTickets(currentStatus, currentKeyword);
    });


    function openTransferModal(ticketId, unit) {
        document.getElementById('transfer_ticket_id').value = ticketId;
        loadStaffByUnit(unit);
        document.getElementById('transferModal').classList.remove('hidden');
    }

    function closeTransferModal() {
        document.getElementById('transferModal').classList.add('hidden');
        document.getElementById('to_staff').innerHTML = '';
        document.getElementById('reason').value = '';
    }

    async function loadStaffByUnit(unit) {
        try {
            const res = await fetch(`/tickets/staff/unit/${encodeURIComponent(unit)}`);
            if (!res.ok) throw new Error('Gagal memuat pegawai');
            const staffList = await res.json();

            const select = document.getElementById('to_staff');
            select.innerHTML = '<option value="">-- Pilih Pegawai --</option>';
            staffList.forEach(s => {
                select.innerHTML += `<option value="${s.id}">${s.full_name}</option>`;
            });
        } catch (e) {
            alert(e.message);
        }
    }

    document.getElementById('transferForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const ticketId = document.getElementById('transfer_ticket_id').value;
        const toStaffId = document.getElementById('to_staff').value;
        const reason = document.getElementById('reason').value.trim();

        if (!toStaffId) {
            alert('Pilih pegawai tujuan alihkan');
            return;
        }
        if (!reason) {
            alert('Isi alasan alihkan tiket');
            return;
        }

        try {
            const res = await fetch('/tickets/transfer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ticket_id: ticketId,
                    to_staff_id: toStaffId,
                    reason: reason
                })
            });
            const data = await res.json();

            alert(data.message);
            if (data.status === 'success') {
                closeTransferModal();
                fetchTickets('open');
                fetchTickets('inprogress');
            }
        } catch (error) {
            alert('Gagal mengalihkan tiket');
        }
    });

    async function takeTicket(ticketId) {
        if (!confirm('Yakin ingin mengambil tiket ini?')) return;

        try {
            const res = await fetch('/tickets/take', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ticket_id: ticketId
                })
            });
            const data = await res.json();
            alert(data.message);
            if (data.status === 'success') {
                fetchTickets('open');
                fetchTickets('inprogress');
                fetchTickets('transfer');
            }
        } catch {
            alert('Gagal mengambil tiket');
        }
    }

    async function acceptTransfer(transferId) {
        if (!confirm('Terima tiket ini?')) return;

        const res = await fetch('/tickets/transfer/respond', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                transfer_id: transferId,
                response: 'accept'
            })
        });
        const data = await res.json();

        alert(data.message);
        if (data.status === 'success') {
            fetchTickets('transfer');
            fetchTickets('inprogress');
        }
    }

    async function rejectTransfer(transferId) {
        const reason = prompt('Alasan menolak tiket:');
        if (reason === null || reason.trim() === '') {
            alert('Alasan penolakan wajib diisi!');
            return;
        }

        const res = await fetch('/tickets/transfer/respond', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                transfer_id: transferId,
                response: 'reject',
                reason
            })
        });
        const data = await res.json();

        alert(data.message);
        if (data.status === 'success') {
            fetchTickets('transfer');
            fetchTickets('menunggu');
        }
    }


    document.addEventListener('DOMContentLoaded', () => {
        statusButtons.forEach(btn => {
            fetchTicketCount(btn.dataset.status);
        });
        statusButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                fetchTickets(currentStatus, currentKeyword);
                fetchTicketCount(currentStatus);
            });
        });
        fetchTickets(currentStatus);
    });
</script>

<?= $this->endSection() ?>