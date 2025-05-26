<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-blue-900 border-b border-blue-300 pb-2 select-none">Daftar Tiket Unit Saya</h2>

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

<!-- Modal Detail Tiket -->
<div id="ticketDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg max-w-lg w-full max-h-[90vh] overflow-y-auto relative">
        <button id="closeModal" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-3xl font-bold leading-none">&times;</button>
        <h3 class="text-2xl font-semibold mb-4 text-blue-900 select-none px-6 pt-6">Detail Tiket</h3>
        <div id="ticketDetails" class="px-6 pb-6 text-gray-800 text-sm space-y-4">
            <!-- Detail tiket akan dimuat disini -->
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


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

        // Load tiket sesuai status
        function loadTickets(status) {
            currentStatus = status;
            $ticketsContainer.empty();
            $noTickets.hide();
            $loading.show();

            $.getJSON("<?= base_url('tickets/list-for-unit') ?>", function(res) {
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

        // Event klik tab status
        $('.status-tab').click(function() {
            $('.status-tab').removeClass('bg-blue-600 text-white').addClass('bg-gray-100 text-gray-700 hover:bg-gray-200');
            $(this).addClass('bg-blue-600 text-white').removeClass('bg-gray-100 text-gray-700 hover:bg-gray-200');

            const status = $(this).data('status');
            loadTickets(status);
        });

        // Load default tab 'Open'
        loadTickets(currentStatus);

        // Event ambil tiket
        $ticketsContainer.on('click', '.take-ticket-btn', function() {
            const idTiket = $(this).data('id');

            Swal.fire({
                title: 'Yakin ambil tiket ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, ambil tiket',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("<?= base_url('tickets/take') ?>", {
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

        // Event detail tiket
        $ticketsContainer.on('click', '.detail-btn', function() {
            const idTiket = $(this).data('id');
            $.getJSON(`<?= base_url('tickets/detail') ?>/${idTiket}`, function(response) {
                if (response.status === 'success') {
                    const data = response.data;
                    let imgHtml = data.gambar ?
                        `<img src="<?= base_url('uploads/') ?>${encodeURIComponent(data.gambar)}" alt="Gambar Tiket" class="w-full h-64 object-cover rounded-t-lg">` :
                        '<p class="italic text-gray-500">Tidak ada gambar.</p>';

                    let html = `
                    <div class="space-y-6">
                        <!-- Gambar tiket -->
                        <div class="w-full mb-4">
                            ${imgHtml}
                        </div>

                        <!-- Informasi tiket -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Kiri -->
                            <div class="space-y-2">
                                <p><span class="font-semibold">Judul:</span> ${data.judul}</p>
                                <p><span class="font-semibold">Deskripsi:</span> ${data.deskripsi}</p>
                            </div>
                            <!-- Kanan -->
                            <div class="space-y-2">
                                <p><span class="font-semibold">Prioritas:</span> <span class="text-${data.prioritas.toLowerCase()}-600">${data.prioritas}</span></p>
                                <p><span class="font-semibold">Status:</span> <span class="px-2 py-1 rounded bg-${data.status === 'Closed' ? 'gray' : (data.status === 'Done' ? 'blue' : 'yellow')}-100 text-${data.status === 'Closed' ? 'gray' : (data.status === 'Done' ? 'blue' : 'yellow')}-600 text-xs font-semibold">${data.status}</span></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <p><span class="font-semibold">Dibuat oleh:</span> ${data.requestor_nama} (${data.requestor_email})</p>
                                <p><span class="font-semibold">Tanggal Dibuat:</span> ${data.created_at}</p>
                            </div>
                            <div class="space-y-2">
                                <p><span class="font-semibold">Ditugaskan kepada:</span> ${data.assigned_nama || 'Tidak ditugaskan'}</p>
                            </div>
                        </div>

                        <hr class="my-3" />

                        <!-- Komentar Penyelesaian dan Rating -->
                        <div class="space-y-3">
                            <h4 class="font-semibold text-lg text-blue-900">Komentar Penyelesaian:</h4>
                            <p class="italic text-gray-600">${data.komentar_penyelesaian || 'Tidak ada komentar.'}</p>
                            <h4 class="font-semibold text-lg text-blue-900">Rating:</h4>
 <p><span class="font-semibold">Waktu:</span> ${data.rating_time ? ratingTimeText(data.rating_time) : '-'}</p>
    <p><span class="font-semibold">Layanan:</span> ${data.rating_service ? ratingServiceText(data.rating_service) : '-'}</p>                        </div>
                    </div>
                `;

                    $('#ticketDetails').html(html);
                    $('#ticketDetailModal').removeClass('hidden');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Gagal memuat detail tiket'
                    });
                }
            });
        });

        // Close modal
        $('#closeModal').on('click', function() {
            $('#ticketDetailModal').addClass('hidden');
            $('#ticketDetails').html('');
        });
    });
</script>

<?= $this->endSection() ?>