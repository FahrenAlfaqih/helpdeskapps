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
            <label for="status" class="block font-semibold mb-1">Status Penyelesaian</label>
            <select id="status" name="status" required class="w-full border rounded px-3 py-2 mb-4">
                <option value="Open">Belum Selesai</option>
                <option value="Closed">Sudah Selesai</option>
            </select>
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

        <!-- Loading Spinner -->
        <div id="loading" class="hidden fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center z-50">
            <div class="animate-spin rounded-full border-t-4 border-blue-600 h-16 w-16 mb-4"></div>
            <p class="text-white">Sedang memproses...</p>
        </div>
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
                        `<img src="<?= base_url('uploads/') ?>${encodeURIComponent(data.gambar)}" alt="Gambar Tiket" class="w-full h-64 object-cover rounded-lg mb-6 border border-gray-300">` :
                        '<p class="italic text-gray-500">Tidak ada gambar.</p>';

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
  </div>



    </div>

    <hr class="border-gray-300 my-6">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <div>
            <h4 class="font-semibold mb-2 text-blue-900">Penugasan</h4>
            <p><span class="font-semibold">Ditugaskan kepada:</span> ${data.assigned_nama || 'Tidak ditugaskan'}</p>
            <p><span class="font-semibold">Telepon 1:</span> ${data.assigned_telpon1 || '-'}</p>
            <p><span class="font-semibold">Telepon 2:</span> ${data.assigned_telpon2 || '-'}</p>
            <p><span class="font-semibold">Ruangan:</span> ${data.nm_ruangan}</p>
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

    <p class="text-right text-xs text-gray-500 mt-6">Dibuat pada: ${data.created_at}</p>
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

        $('#status').on('change', function() {
            const selected = $(this).val();
            if (selected === 'Closed') {
                $('#rating_service').prop('required', true).closest('.mb-4').show();
                $('#rating_time').prop('required', true).closest('.mb-6').show();
            } else {
                $('#rating_service').prop('required', false).val('').closest('.mb-4').hide();
                $('#rating_time').prop('required', false).val('').closest('.mb-6').hide();
            }
        });

        $('#ticketsTable tbody').on('click', '.confirm-btn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            $('#confirm_id_tiket').val(id);
            $('#komentar_penyelesaian').val('');
            $('#rating_service').val('');
            $('#rating_time').val('');
            $('#status').val('Open').trigger('change');
            $('#confirmCompletionModal').removeClass('hidden');
        });

        $('#closeConfirmModal').on('click', function() {
            $('#confirmCompletionModal').addClass('hidden');
        });

        $('#confirmCompletionForm').on('submit', function(e) {
            e.preventDefault();
            $('#loading').removeClass('hidden');
            const formData = $(this).serialize();

            $.post("<?= base_url('tickets/confirm') ?>", formData, function(res) {
                if (res.status === 'success') {
                    $('#loading').addClass('hidden');
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
                    $('#loading').addClass('hidden');
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