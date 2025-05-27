<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-3xl font-bold mb-8 text-blue-900 border-b border-blue-300 pb-2 select-none">Daftar Tiket Saya</h2>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-800 rounded shadow-sm">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <a href="<?= base_url('tickets/create') ?>"
        class="inline-block mb-6 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition duration-200">
        Buat Tiket Baru
    </a>

    <div class="overflow-x-auto rounded-lg">
        <table id="ticketsTable" class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-blue-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Judul</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">SubKategori</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Prioritas</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Tanggal Dibuat</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-blue-700 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <!-- DataTables akan render di sini -->
            </tbody>
        </table>
    </div>
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

<!-- Modal Konfirmasi Penyelesaian -->
<div id="confirmCompletionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg max-w-lg w-full max-h-[90vh] overflow-y-auto relative p-6">
        <button id="closeConfirmModal" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-3xl font-bold leading-none">&times;</button>
        <h3 class="text-2xl font-semibold mb-6 text-blue-900 select-none">Konfirmasi Penyelesaian Tiket</h3>

        <form id="confirmCompletionForm">
            <input type="hidden" name="id_tiket" id="confirm_id_tiket" />

            <label for="komentar_penyelesaian" class="block font-semibold mb-1">Komentar Penyelesaian</label>
            <textarea id="komentar_penyelesaian" name="komentar_penyelesaian" rows="4" required
                class="w-full border rounded px-3 py-2 mb-4"></textarea>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Rating Service</label>
                <select name="rating_service" id="rating_service" required class="w-full border rounded px-3 py-2">
                    <option value="">-- Pilih Rating Service --</option>
                    <option value="1">1 - Buruk</option>
                    <option value="2">2 - Cukup</option>
                    <option value="3">3 - Baik</option>
                    <option value="4">4 - Sangat Baik</option>
                    <option value="5">5 - Luar Biasa</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block font-semibold mb-1">Rating Waktu Penyelesaian</label>
                <select name="rating_time" id="rating_time" required class="w-full border rounded px-3 py-2">
                    <option value="">-- Pilih Rating Waktu --</option>
                    <option value="1">1 - Sangat Lama</option>
                    <option value="2">2 - Lama</option>
                    <option value="3">3 - Cukup</option>
                    <option value="4">4 - Cepat</option>
                    <option value="5">5 - Sangat Cepat</option>
                </select>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition duration-200">
                Kirim Konfirmasi
            </button>
        </form>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet" />
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    $(document).ready(function() {
        $('#ticketsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "<?= base_url('tickets/list') ?>",
            columns: [{
                    data: 'judul'
                },
                {
                    data: 'nama_kategori'
                },
                {
                    data: 'nama_subkategori'
                },
                {
                    data: 'prioritas'
                },
                {
                    data: 'status'
                },
                {
                    data: 'created_at'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        let btn = `<button class="detail-btn bg-blue-600 text-white px-3 py-1 rounded mr-2 hover:bg-blue-700 transition" data-id="${row.id_tiket}">Detail</button>`;
                        if (row.status === 'Done' && row.confirm_by_requestor == 0) {
                            btn += `<a href="#" class="confirm-btn text-blue-600 hover:underline font-semibold" data-id="${row.id_tiket}">Konfirmasi</a>`;
                        }
                        return btn;
                    }
                }
            ],
            lengthMenu: [10, 25, 50],
            language: {
                emptyTable: "Tidak ada data tiket yang tersedia",
                processing: "Memuat data...",
                lengthMenu: "Tampilkan _MENU_ tiket",
                search: "Cari:",
                paginate: {
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        });


        $('#ticketsTable tbody').on('click', '.detail-btn', function() {
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
                            <p><span class="font-semibold">Kategori:</span> ${data.nama_kategori || '-'}</p>
                            <p><span class="font-semibold">Sub Kategori:</span> ${data.nama_subkategori || '-'}</p>
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
                        <p><span class="font-semibold">Layanan:</span> ${data.rating_service ? ratingServiceText(data.rating_service) : '-'}</p>
                    </div>
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


        // Tutup modal
        $('#closeModal').on('click', function() {
            $('#ticketDetailModal').addClass('hidden');
            $('#ticketDetails').html('');
        });

        // Fungsi bantu (kalau belum ada, tambahin aja! Atau ganti sesuai logika lo)
        function ratingTimeText(rating) {
            switch (parseInt(rating)) {
                case 1:
                    return "Sangat Lambat";
                case 2:
                    return "Lambat";
                case 3:
                    return "Cukup";
                case 4:
                    return "Cepat";
                case 5:
                    return "Sangat Cepat";
                default:
                    return "-";
            }
        }

        function ratingServiceText(rating) {
            switch (parseInt(rating)) {
                case 1:
                    return "Sangat Buruk";
                case 2:
                    return "Buruk";
                case 3:
                    return "Cukup";
                case 4:
                    return "Baik";
                case 5:
                    return "Sangat Baik";
                default:
                    return "-";
            }
        }


        // Saat tombol konfirmasi diklik, buka modal dan isi id tiket
        $('#ticketsTable tbody').on('click', '.confirm-btn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            $('#confirm_id_tiket').val(id);
            $('#komentar_penyelesaian').val('');
            $('#rating_service').val('');
            $('#rating_time').val('');
            $('#confirmCompletionModal').removeClass('hidden');
        });

        // Close modal konfirmasi
        $('#closeConfirmModal').on('click', function() {
            $('#confirmCompletionModal').addClass('hidden');
        });

        // Close modal detail
        $('#closeModal').on('click', function() {
            $('#ticketDetailModal').addClass('hidden');
            $('#ticketDetails').html('');
        });

        // Submit form konfirmasi
        $('#confirmCompletionForm').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.post("<?= base_url('tickets/confirm') ?>", formData, function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message || 'Konfirmasi berhasil',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    $('#confirmCompletionModal').addClass('hidden');
                    $('#ticketsTable').DataTable().ajax.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Gagal mengkonfirmasi tiket',
                    });
                }
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal mengirim data konfirmasi',
                });
            });
        });
    });
</script>


<?= $this->endSection() ?>