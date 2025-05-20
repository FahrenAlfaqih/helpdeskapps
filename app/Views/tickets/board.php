<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto p-4">

    <h1 class="text-2xl font-bold mb-6 text-blue-700">Tiket Board - Kepala <?= ($roleId == 2) ? 'IT' : 'GA' ?></h1>

    <!-- Tombol Status -->
    <div id="statusButtons" class="flex space-x-3 mb-6">
        <?php
        $statuses = [
            'menunggu' => 'Menunggu',
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
            <p class="text-center text-blue-600 font-semibold mt-6">Memuat tiket...</p>
        </div>
    </div>

</div>

<script>
    const statusButtons = document.querySelectorAll('#statusButtons button');
    const ticketListEl = document.getElementById('ticketList');
    const searchInput = document.getElementById('searchInput');

    let currentStatus = 'menunggu'; // default status aktif
    let currentKeyword = '';

    // Fungsi fetch tiket sesuai status dan keyword
    async function fetchTickets(status, keyword = '') {
        const ticketListEl = document.getElementById('ticketList');
        ticketListEl.innerHTML = '<p class="text-center text-blue-600 font-semibold mt-6">Memuat tiket...</p>';

        try {
            const res = await fetch(`/tickets/board/list/${status}`);
            if (!res.ok) throw new Error('Gagal memuat tiket');
            let tickets = await res.json();

            if (keyword) {
                tickets = tickets.filter(t => t.title.toLowerCase().includes(keyword.toLowerCase()));
            }

            if (tickets.length === 0) {
                ticketListEl.innerHTML = '<p class="text-center text-blue-600 font-semibold mt-6">Tidak ada tiket ditemukan.</p>';
                return;
            }

            ticketListEl.innerHTML = '';
            tickets.forEach(ticket => {
                // Highlight deadline color (red if <= 3 days, orange if <= 7 days)
                let deadlineColor = 'text-gray-600';
                if (ticket.deadline) {
                    const today = new Date();
                    const deadlineDate = new Date(ticket.deadline);
                    const diffDays = Math.ceil((deadlineDate - today) / (1000 * 60 * 60 * 24));
                    if (diffDays <= 3 && diffDays >= 0) deadlineColor = 'text-red-600 font-semibold';
                    else if (diffDays <= 7 && diffDays > 3) deadlineColor = 'text-orange-500 font-semibold';
                }

                const card = document.createElement('div');
                card.className = `
                bg-white border border-blue-200 rounded-lg p-5 shadow-md 
                hover:shadow-lg transition cursor-pointer flex flex-col justify-between
                group
            `;

                card.innerHTML = `
                <div>
                    <h3 class="text-blue-900 font-bold text-lg mb-2 group-hover:text-blue-700 transition">${escapeHtml(ticket.title)}</h3>
                    <p class="text-sm text-gray-500 mb-1"> ${new Date(ticket.created_at).toLocaleDateString()}</p>
                    <p class="text-sm ${deadlineColor} mb-1">  <strong>Deadline: </strong> : ${ticket.deadline ? new Date(ticket.deadline).toLocaleDateString() : '-'}</p>
                    <p class="text-sm text-gray-700 mb-1"> <strong>Kategori: </strong>  ${escapeHtml(ticket.kategori || '-')}</p>
                    <p class="text-sm text-gray-700"> <strong>Ruangan: </strong> ${escapeHtml(ticket.ruangan_name || '-')}</p>
                </div>
                <div class="flex justify-end  mt-4">
                    <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wide select-none">
                        ${escapeHtml(ticket.status)}
                    </span>
                </div>
            `;

                card.onclick = () => {
                    window.location.href = '/tickets/detail/' + ticket.id;
                };

                ticketListEl.appendChild(card);
            });

        } catch (err) {
            ticketListEl.innerHTML = `<p class="text-center text-red-600 font-semibold mt-6">${err.message}</p>`;
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

    // Handler klik tombol status
    statusButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.dataset.status === currentStatus) return; // skip kalau sama

            currentStatus = btn.dataset.status;

            // Update style tombol aktif
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

    // Handler input search tiket
    searchInput.addEventListener('input', (e) => {
        currentKeyword = e.target.value.trim();
        fetchTickets(currentStatus, currentKeyword);
    });

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