<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-md">



    <h2 class="text-3xl font-bold mb-6 text-blue-900 border-b border-blue-300 pb-2 select-none">Daftar Tiket Unit Saya</h2>
    <!-- Filter Unit Usaha -->
    <?php if (in_array(session('unit_level_id'), ['A8', 'A7'])): ?>
        <div class="mb-6">
            <label for="filterUnitUsaha" class="block mb-2 font-semibold text-gray-700">Filter Berdasarkan Unit Usaha:</label>
            <select id="filterUnitUsaha" class="border px-3 py-2 rounded w-full max-w-sm">
                <option value="">-- Semua Unit Usaha --</option>
                <?php foreach ($unitUsahaList as $usaha): ?>
                    <option value="<?= esc($usaha['id_unit_usaha']) ?>"><?= esc($usaha['nm_unit_usaha']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>


    <!-- Tabs Status -->
    <div class="flex space-x-4 mb-6 border-b border-gray-300">
        <?php
        $statuses = ['Open', 'In Progress', 'Done', 'Closed'];
        ?>
        <?php foreach ($statuses as $i => $status): ?>
            <button
                class="status-tab px-4 py-2 font-semibold rounded-t-lg cursor-pointer
                    <?= $i === 0 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>"
                data-status="<?= $status ?>">
                <?= $status ?>
            </button>
        <?php endforeach ?>
    </div>

    <!-- Card Container -->
    <div id="ticketsContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 min-h-[200px]">
        <!-- Cards akan muncul di sini -->
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="hidden text-center text-gray-500 mt-4">Memuat tiket...</div>
    <div id="noTicketsMessage" class="hidden text-center text-gray-500 mt-4">Tidak ada tiket untuk status ini.</div>
</div>

<div id="ticketDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto relative">
        <button id="closeModal" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-3xl font-bold leading-none">&times;</button>
        <h3 class="text-2xl font-semibold mb-4 text-blue-900 select-none px-6 pt-6">Detail Tiket</h3>
        <div id="ticketDetails" class="px-6 pb-6 text-gray-800 text-sm space-y-4">
            <!-- Detail tiket akan dimuat disini -->
        </div>
    </div>
</div>

<!-- Modal Ambil Tiket (Staff) -->
<div id="takeTicketModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg max-w-lg w-full max-h-[90vh] overflow-y-auto relative p-6">
        <button id="closeTakeTicketModal" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-3xl font-bold leading-none">&times;</button>
        <h3 class="text-2xl font-semibold mb-6 text-blue-900 select-none">Ambil Tiket</h3>

        <form id="takeTicketForm">
            <input type="hidden" name="id_tiket" id="take_id_tiket" />

            <label for="status" class="block font-semibold mb-1">Status</label>
            <select id="status" name="status" required class="w-full border rounded px-3 py-2 mb-4">
                <option value="In Progress" selected>In Progress</option>
                <option value="Done">Done</option>
            </select>

            <label for="prioritas" class="block font-semibold mb-1">Prioritas</label>
            <select id="prioritas" name="prioritas" required class="w-full border rounded px-3 py-2 mb-4">
                <option value="High">High</option>
                <option value="Medium" selected>Medium</option>
                <option value="Low">Low</option>
            </select>

            <label for="komentar_staff" class="block font-semibold mb-1">Komentar Staff</label>
            <textarea id="komentar_staff" name="komentar_staff" rows="4" placeholder="Masukkan komentar..." class="w-full border rounded px-3 py-2 mb-6"></textarea>

            <div class="flex justify-end space-x-3">
                <button type="button" id="cancelTakeTicket" class="px-5 py-2 rounded border border-gray-300 hover:bg-gray-100 transition">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition duration-200">Ambil Tiket</button>
            </div>
        </form>

        <div id="loading" class="hidden fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center z-50">
            <div class="animate-spin rounded-full border-t-4 border-blue-600 h-16 w-16 mb-4"></div>
            <p class="text-white">Sedang memproses...</p>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>



<script>
    $(function() {
        const $ticketsContainer = $('#ticketsContainer');
        const $loading = $('#loadingSpinner');
        const $noTickets = $('#noTicketsMessage');
        let currentStatus = 'Open';

        function ratingTimeText(value) {
            const map = {
                1: 'Sangat Lama',
                2: 'Lama',
                3: 'Cukup',
                4: 'Cepat',
                5: 'Sangat Cepat'
            };
            return map[value] || '-';
        }

        function ratingServiceText(value) {
            const map = {
                1: 'Buruk',
                2: 'Cukup',
                3: 'Baik',
                4: 'Sangat Baik',
                5: 'Luar Biasa'
            };
            return map[value] || '-';
        }

        // Fungsi render card tiket
        function renderTicketCard(ticket) {
            const assignedText = ticket.assigned_nama ?
                `: ${ticket.assigned_nama}` :
                ': Belum ditugaskan';
            const statusColors = {
                'Open': 'bg-green-100 text-green-800',
                'In Progress': 'bg-yellow-100 text-yellow-800',
                'Done': 'bg-blue-100 text-blue-800',
                'Closed': 'bg-gray-100 text-gray-600'
            };
            const priorityColors = {
                'High': 'text-red-600',
                'Medium': 'text-yellow-600',
                'Low': 'text-green-600'
            };
            const statusColor = statusColors[ticket.status] || 'bg-gray-100 text-gray-600';
            const priorityColor = priorityColors[ticket.prioritas] || 'text-gray-700';

            let btns = `<button class="detail-btn bg-blue-600 text-white px-3 py-1 rounded mr-2 hover:bg-blue-700 transition" data-id="${ticket.id_tiket}">Detail</button>`;

            if (!ticket.assigned_to) {
                btns += `<button class="take-ticket-btn bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition" data-id="${ticket.id_tiket}">Ambil Tiket</button>`;
            } else if (ticket.assigned_to === "<?= session()->get('id_pegawai') ?>") {
                if (ticket.status === 'In Progress') {
                    btns += `<button class="finish-ticket-btn bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition" data-id="${ticket.id_tiket}">Selesai</button>`;
                }
            } else {
                btns += `<span class="text-gray-600 italic">Diambil orang lain</span>`;
            }

            return `
        <div class="bg-white rounded-lg shadow p-4 border border-gray-200 flex flex-col justify-between">
            <div>
                <h4 class="text-lg font-bold mb-2 text-blue-900">${ticket.judul}</h4>
                <p class="mb-1"><span class="font-semibold">Prioritas:</span> <span class="${priorityColor}">${ticket.prioritas}</span></p>
                <p class="mb-1"><span class="font-semibold">Status:</span> <span class="px-2 py-1 rounded ${statusColor} text-xs font-semibold">${ticket.status}</span></p>
                <p class="mb-1"><span class="font-semibold">Requestor:</span> ${ticket.requestor_nama}</p>
                <p class="mb-1"><span class="font-semibold">Ditugaskan kepada</span>${assignedText}</p>
                <p class="mb-1"><span class="font-semibold">Tanggal Dibuat:</span> ${ticket.created_at}</p>
            </div>
            <div class="mt-3 flex flex-wrap items-center">
                ${btns}
            </div>
        </div>
        `;
        }


        function loadTickets(status) {
            currentStatus = status;
            $ticketsContainer.empty();
            $noTickets.hide();
            $loading.show();

            const unitUsaha = $('#filterUnitUsaha').val();

            $.getJSON("<?= base_url('tickets/list-for-unit') ?>", {
                unit_usaha: unitUsaha
            }, function(res) {
                $loading.hide();
                if (res.data && res.data.length) {
                    // Filter berdasarkan status
                    let filtered = res.data.filter(t => t.status === status);
                    if (filtered.length === 0) {
                        $noTickets.show();
                    } else {
                        filtered.forEach(t => {
                            $ticketsContainer.append(renderTicketCard(t));
                        });
                    }
                } else {
                    $noTickets.show();
                }
            });
        }

        $('#filterUnitUsaha').on('change', function() {
            loadTickets(currentStatus);
        });

        // Event klik tab status
        $('.status-tab').click(function() {
            $('.status-tab').removeClass('bg-blue-600 text-white').addClass('bg-gray-100 text-gray-700 hover:bg-gray-200');
            $(this).addClass('bg-blue-600 text-white').removeClass('bg-gray-100 text-gray-700 hover:bg-gray-200');

            const status = $(this).data('status');
            loadTickets(status);
        });

        // Load default tab 'Open'
        loadTickets(currentStatus);

        $(function() {
            const $ticketsContainer = $('#ticketsContainer');
            const $modal = $('#takeTicketModal');
            const $form = $('#takeTicketForm');

            $ticketsContainer.on('click', '.take-ticket-btn', function() {
                const idTiket = $(this).data('id');
                $('#take_id_tiket').val(idTiket);
                $form[0].reset();
                $modal.removeClass('hidden');
            });

            $('#closeTakeTicketModal, #cancelTakeTicket').on('click', function() {
                $modal.addClass('hidden');
            });

            $form.on('submit', function(e) {
                e.preventDefault();
                $('#loading').removeClass('hidden');

                const status = $('#status').val();
                const komentar = $('#komentar_staff').val().trim();

                // Validasi komentar wajib jika status Done
                if (status === 'Done' && komentar.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Komentar wajib diisi',
                        text: 'Silakan isi komentar staff saat memilih status Done.'
                    });
                    return;
                }

                const formData = $(this).serialize();

                $.post("<?= base_url('tickets/take') ?>", formData, function(response) {
                    $('#loading').addClass('hidden');
                    Swal.fire({
                        icon: response.status === 'success' ? 'success' : 'error',
                        title: response.status === 'success' ? 'Berhasil!' : 'Gagal!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    if (response.status === 'success') {
                        $modal.addClass('hidden');
                        loadTickets(currentStatus);
                    }
                }, 'json').fail(function(xhr) {
                    $('#loading').addClass('hidden');
                    Swal.fire('Error', 'Terjadi kesalahan saat mengambil tiket', 'error');
                });
            });
        });


        // Event selesai tiket
        $ticketsContainer.on('click', '.finish-ticket-btn', function() {
            const idTiket = $(this).data('id');

            Swal.fire({
                title: 'Apakah tiket sudah selesai dikerjakan?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, selesai',
                cancelButtonText: 'Belum',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("<?= base_url('tickets/finish') ?>", {
                        id_tiket: idTiket
                    }, function(response) {
                        Swal.fire({
                            icon: response.status === 'success' ? 'success' : 'error',
                            title: response.status === 'success' ? 'Berhasil!' : 'Gagal!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        loadTickets(currentStatus);
                    }, 'json');
                }
            });
        });

        $ticketsContainer.on('click', '.detail-btn', function() {
            const idTiket = $(this).data('id');
            $.getJSON(`<?= base_url('tickets/detail') ?>/${idTiket}`, function(response) {
                if (response.status === 'success') {
                    const data = response.data;
                    let imgHtml = data.gambar ?
                        `<img src="<?= base_url('uploads/') ?>${encodeURIComponent(data.gambar)}" alt="Gambar Tiket" class="w-full h-64 object-cover rounded-lg mb-6 border border-gray-300">` :
                        '<p class="italic text-gray-500">Tidak ada gambar.</p>';

                    // Histori Petugas
                    let assigneesHtml = '';
                    if (data.assignees && data.assignees.length > 0) {
                        assigneesHtml = `
                <h4 class="font-semibold mb-2 text-blue-900">Histori Petugas</h4>
                <div class="overflow-x-auto">
                <table class="min-w-full border text-xs text-left mb-3">
                    <thead>
                        <tr>
                            <th class="py-2 px-3 border-b">#</th>
                            <th class="py-2 px-3 border-b">Nama Petugas</th>
                            <th class="py-2 px-3 border-b">Telepon 1</th>
                            <th class="py-2 px-3 border-b">Waktu Mulai</th>
                            <th class="py-2 px-3 border-b">Waktu Selesai</th>
                            <th class="py-2 px-3 border-b">Komentar</th>
                            <th class="py-2 px-3 border-b">Rating Waktu</th>
                            <th class="py-2 px-3 border-b">Rating Layanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.assignees.map((a, idx) => `
                            <tr>
                                <td class="py-2 px-3 border-b">${a.sequence}</td>
                                <td class="py-2 px-3 border-b">${a.assigned_nama || '-'}</td>
                                <td class="py-2 px-3 border-b">${a.assigned_telpon1 || '-'}</td>
                                <td class="py-2 px-3 border-b">${a.assigned_at ? dayjs(a.assigned_at).format('DD MMM YYYY HH:mm') : '-'}</td>
                                <td class="py-2 px-3 border-b">${a.finished_at ? dayjs(a.finished_at).format('DD MMM YYYY HH:mm') : '-'}</td>
                                <td class="py-2 px-3 border-b">${a.komentar_penyelesaian || '-'}</td>
                                <td class="py-2 px-3 border-b">${a.rating_time ? ratingTimeText(a.rating_time) : '-'}</td>
                                <td class="py-2 px-3 border-b">${a.rating_service ? ratingServiceText(a.rating_service) : '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                </div>
                `;
                    } else {
                        assigneesHtml = `<p class="italic text-gray-500">Belum ada histori petugas.</p>`;
                    }

                    // Fungsi format durasi
                    function formatDuration(seconds) {
                        if (!seconds || seconds < 0) return '-';
                        const d = Math.floor(seconds / 86400);
                        const h = Math.floor((seconds % 86400) / 3600);
                        const m = Math.floor((seconds % 3600) / 60);
                        const s = seconds % 60;
                        let str = '';
                        if (d) str += d + ' hari ';
                        if (h) str += h + ' jam ';
                        if (m) str += m + ' menit ';
                        if (s || (!d && !h && !m)) str += s + ' detik';
                        return str.trim();
                    }

                    // Info waktu pengerjaan
                    const lastAssignee = data.assignees && data.assignees.length > 0 ? data.assignees[data.assignees.length - 1] : null;
                    let waktuPengerjaanHtml = '';
                    if (lastAssignee) {
                        let assignedAt = lastAssignee.assigned_at ? dayjs(lastAssignee.assigned_at).format('D MMM YYYY, HH:mm') : '-';
                        let finishedAt = lastAssignee.finished_at ? dayjs(lastAssignee.finished_at).format('D MMM YYYY, HH:mm') : '-';
                        let durasi = '-';
                        if (lastAssignee.assigned_at && lastAssignee.finished_at) {
                            let dur = dayjs(lastAssignee.finished_at).diff(dayjs(lastAssignee.assigned_at), 'second');
                            durasi = formatDuration(dur);
                        }
                        waktuPengerjaanHtml = `
                    <h4 class="font-semibold mb-2 text-blue-900">Waktu Pengerjaan</h4>
                    <p><span class="font-semibold">Tiket diproses</span> : ${assignedAt}</p>
                    <p><span class="font-semibold">Tiket selesai</span> : ${finishedAt}</p>
                    <p><span class="font-semibold">Durasi pengerjaan</span> : ${durasi}</p>
                `;
                    }

                    let html = `
<div class="space-y-6 text-gray-800 text-sm">
    ${imgHtml}

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <h4 class="font-semibold mb-2 text-blue-900">Informasi Requestor & Penempatan</h4>
            <p><span class="font-semibold">Dibuat oleh:</span> ${data.requestor_nama}</p>
            <p><span class="font-semibold">Telepon 1:</span> ${data.requestor_telpon1 || '-'}</p>
            <p><span class="font-semibold">Telepon 2:</span> ${data.requestor_telpon2 || '-'}</p>
            <p><span class="font-semibold">Email:</span> ${data.requestor_email || '-'}</p>
            <p><span class="font-semibold mt-4 block">Penempatan:</span></p>
            <ul class="list-disc list-inside ml-5 space-y-0.5 text-sm">
                <li><strong>Level:</strong> ${data.req_penempatan.unit_level}</li>
                <li><strong>Bisnis:</strong> ${data.req_penempatan.unit_bisnis}</li>
                <li><strong>Usaha:</strong> ${data.req_penempatan.unit_usaha}</li>
                <li><strong>Organisasi:</strong> ${data.req_penempatan.unit_organisasi}</li>
                <li><strong>Kerja:</strong> ${data.req_penempatan.unit_kerja}</li>
                <li><strong>Kerja Sub:</strong> ${data.req_penempatan.unit_kerja_sub}</li>
                <li><strong>Lokasi:</strong> ${data.req_penempatan.unit_lokasi}</li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold mb-2 text-blue-900">Informasi Tiket</h4>
            <p><span class="font-semibold">Judul:</span> ${data.judul}</p>
            <p><span class="font-semibold">Deskripsi:</span></p>
            <div class="prose max-w-none text-gray-800">${data.deskripsi}</div>
            <p><span class="font-semibold">Prioritas:</span> <span class="text-${data.prioritas.toLowerCase()}-600 font-semibold">${data.prioritas}</span></p>
            <p><span class="font-semibold">Status:</span> <span class="inline-block px-2 py-1 rounded bg-${data.status === 'Closed' ? 'gray' : (data.status === 'Done' ? 'blue' : 'yellow')}-100 text-${data.status === 'Closed' ? 'gray' : (data.status === 'Done' ? 'blue' : 'yellow')}-600 text-xs font-semibold">${data.status}</span></p>
            <p><span class="font-semibold">Waktu Penugasan:</span> ${data.updated_at}</p>
        </div>
    </div>
    <hr class="border-gray-300 my-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
            <h4 class="font-semibold mb-2 text-blue-900">Penugasan</h4>
            <p><span class="font-semibold">Ditugaskan kepada:</span> ${data.assigned_nama || 'Tidak ditugaskan'}</p>
            <p><span class="font-semibold">Telepon 1:</span> ${data.assigned_telpon1 || '-'}</p>
            <p><span class="font-semibold">Telepon 2:</span> ${data.assigned_telpon2 || '-'}</p>
            <p><span class="font-semibold">Ruangan:</span> ${data.nm_ruangan}</p>
        </div>
        <div>
            ${waktuPengerjaanHtml}
        </div>
        <div>
            <h4 class="font-semibold mb-2 text-blue-900">Komentar & Rating</h4>
            <p><strong>Komentar Penyelesaian:</strong></p>
            <p class="italic text-gray-600 mb-3">${data.komentar_penyelesaian || 'Tidak ada komentar.'}</p>
            <p><strong>Komentar Staff:</strong></p>
            <p class="italic text-gray-600 mb-3">${data.komentar_staff || 'Tidak ada komentar dari staff.'}</p>
            <p><strong>Rating Waktu:</strong> ${data.rating_time ? ratingTimeText(data.rating_time) : '-'}</p>
            <p><strong>Rating Layanan:</strong> ${data.rating_service ? ratingServiceText(data.rating_service) : '-'}</p>
        </div>
    </div>

    <div class="mt-6">
        ${assigneesHtml}
    </div>

    <p class="text-right text-sm text-gray-500 mt-6">Dibuat pada: ${data.created_at}</p>
</div>
            `;

                    $('#ticketDetails').html(html);
                    $('#ticketDetailModal').removeClass('hidden');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Gagal memuat detail tiket',
                    });
                }
            });
        });

        $('#closeModal').on('click', function() {
            $('#ticketDetailModal').addClass('hidden');
            $('#ticketDetails').html('');
        });
    });
</script>

<?= $this->endSection() ?>