<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-6 text-blue-900">Daftar Tiket Saya</h1>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-3 sm:space-y-0">
        <input
            type="text"
            id="searchInput"
            placeholder="Cari tiket berdasarkan judul..."
            class="border border-blue-300 rounded px-4 py-2 w-full sm:w-1/3 focus:outline-none focus:ring-2 focus:ring-blue-400" />

        <a href="/tickets/create"
            class="inline-block bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition font-semibold text-center">
            Ajukan Tiket Baru
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-blue-300 rounded divide-y divide-blue-200">
            <thead class="bg-blue-100 text-blue-800">
                <tr>
                    <th class="px-4 py-2 text-left">Judul Tiket</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Tujuan</th> <!-- baru -->
                    <th class="px-4 py-2 text-left">Kategori</th> <!-- baru -->
                    <th class="px-4 py-2 text-left">Jenis Perangkat</th> <!-- baru -->
                    <th class="px-4 py-2 text-left">Tanggal Pengajuan</th>
                    <th class="px-4 py-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="ticketsTableBody" class="divide-y divide-blue-200">
                <!-- Data tiket akan dimuat di sini -->
            </tbody>
        </table>

    </div>
</div>

<script>
    const ticketsTableBody = document.getElementById('ticketsTableBody');
    const searchInput = document.getElementById('searchInput');
    let tickets = [];

    async function loadTickets() {
        const res = await fetch('/tickets/list');
        tickets = await res.json();
        renderTickets(tickets);
    }

    function renderTickets(data) {
        ticketsTableBody.innerHTML = '';

        if (data.length === 0) {
            ticketsTableBody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-4 text-blue-600 font-semibold">Tidak ada tiket ditemukan.</td>
      </tr>`;
            return;
        }

        data.forEach(ticket => {
            ticketsTableBody.innerHTML += `
      <tr class="hover:bg-blue-50">
        <td class="px-4 py-2">${escapeHtml(ticket.title)}</td>
        <td class="px-4 py-2 capitalize font-semibold">${escapeHtml(ticket.status)}</td>
        <td class="px-4 py-2">${escapeHtml(ticket.assigned_unit)}</td> <!-- tujuan -->
        <td class="px-4 py-2">${escapeHtml(ticket.kategori || '-')}</td> <!-- kategori -->
        <td class="px-4 py-2">${escapeHtml(ticket.jenis_perangkat_name || '-')}</td> <!-- jenis perangkat -->
        <td class="px-4 py-2">${new Date(ticket.created_at).toLocaleDateString()}</td>
        <td class="px-4 py-2 text-center space-x-2">
          <a href="/tickets/update/${ticket.id}" class="text-blue-600 hover:underline font-medium">Update</a>
          <a href="/tickets/detail/${ticket.id}" class="text-blue-600 hover:underline font-medium">Detail</a>
        </td>
      </tr>
    `;
        });
    }


    searchInput.addEventListener('input', function() {
        const keyword = this.value.trim().toLowerCase();
        const filtered = tickets.filter(t => t.title.toLowerCase().includes(keyword));
        renderTickets(filtered);
    });

    // Fungsi sederhana escape HTML untuk keamanan tampilan
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    loadTickets();
</script>

<?= $this->endSection() ?>