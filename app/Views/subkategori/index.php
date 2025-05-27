<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-3xl font-bold mb-8 text-blue-900 border-b border-blue-300 pb-2 select-none">Daftar Sub Kategori</h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-800 rounded shadow-sm">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <a href="<?= base_url('master/subkategori/create') ?>"
        class="inline-block mb-6 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition duration-200">
        Tambah Sub Kategori Baru
    </a>

    <div class="overflow-x-auto rounded-lg">
        <table id="subkategoriTable" class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-blue-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Sub Kategori</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-blue-700 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subkategori as $sub): ?>
                    <tr>
                        <td class="px-6 py-3"><?= esc($sub['nama_kategori']) ?></td>
                        <td class="px-6 py-3"><?= esc($sub['nama_subkategori']) ?></td>
                        <td class="px-6 py-3 text-center space-x-2">
                            <button class="edit-btn text-blue-600 hover:underline" data-id="<?= esc($sub['id_subkategori']) ?>">Edit</button>
                            <button class="delete-btn text-red-600 hover:underline" data-id="<?= esc($sub['id_subkategori']) ?>">Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit SubKategori -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <button id="closeModal" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-3xl font-bold leading-none">&times;</button>
        <h3 class="text-xl font-semibold mb-4 text-blue-900 select-none">Edit Sub Kategori</h3>

        <form id="editForm">
            <?= csrf_field() ?>
            <input type="hidden" name="id_subkategori" id="edit_id_subkategori" />

            <label for="edit_nama_subkategori" class="block font-semibold mb-1">Nama Sub Kategori</label>
            <input type="text" name="nama_subkategori" id="edit_nama_subkategori" class="w-full border rounded px-3 py-2 mb-4" required />

            <div class="flex justify-end space-x-3">
                <button type="button" id="cancelBtn" class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100 transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 transition">Simpan</button>
            </div>
        </form>

        <div id="editErrors" class="mt-4 text-red-600 text-sm"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        // Edit button click
        $('.edit-btn').on('click', function() {
            let id = $(this).data('id');
            $('#editErrors').html('');
            $.ajax({
                url: `<?= base_url('master/subkategori/edit') ?>/${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        $('#edit_id_subkategori').val(res.data.id_subkategori);
                        $('#edit_nama_subkategori').val(res.data.nama_subkategori);
                        $('#editModal').removeClass('hidden');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal mengambil data subkategori.', 'error');
                }
            });
        });

        // Close modal
        $('#closeModal, #cancelBtn').on('click', function() {
            $('#editModal').addClass('hidden');
            $('#editErrors').html('');
        });

        // Submit edit form via ajax
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            $('#editErrors').html('');
            let id = $('#edit_id_subkategori').val();
            let nama = $('#edit_nama_subkategori').val();
            let csrfToken = $('input[name="<?= csrf_token() ?>"]').val();

            $.ajax({
                url: `<?= base_url('master/subkategori/update') ?>/${id}`,
                method: 'POST',
                data: {
                    nama_subkategori: nama,
                    <?= csrf_token() ?>: csrfToken,
                },
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Sukses', 'SubKategori berhasil diupdate.', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        $('#editErrors').html(res.message || 'Terjadi kesalahan.');
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors || {};
                    let messages = Object.values(errors).flat().join('<br>');
                    $('#editErrors').html(messages);
                }
            });
        });

        // Delete button with Swal confirmation & ajax
        $('.delete-btn').on('click', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `<?= base_url('master/subkategori/delete') ?>/${id}`,
                        method: 'POST',
                        data: {
                            <?= csrf_token() ?>: $('input[name="<?= csrf_token() ?>"]').val()
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                Swal.fire('Berhasil', res.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Gagal', 'Gagal menghapus data.', 'error');
                        }
                    });
                }
            });
        });

    });
</script>

<?= $this->endSection() ?>