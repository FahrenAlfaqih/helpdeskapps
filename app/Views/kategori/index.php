<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-3xl font-bold mb-8 text-blue-900 border-b border-blue-300 pb-2 select-none">Daftar Kategori</h2>

    <a href="<?= base_url('master/kategori/create') ?>"
        class="inline-block mb-6 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition duration-200">
        Tambah Kategori Baru
    </a>

    <div class="overflow-x-auto rounded-lg">
        <table id="kategoriTable" class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-blue-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Nama Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Penanggung Jawab</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-blue-700 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kategori as $kat): ?>
                    <tr>
                        <td class="px-6 py-3"><?= esc($kat['nama_kategori']) ?></td>


                        <td class="px-6 py-3">
                            <?php
                            $pjs = json_decode($kat['penanggung_jawab'] ?? '[]');
                            if (!empty($pjs)) {
                                $namaPJ = array_map(function ($id) use ($mapUnitKerjaSub) {
                                    $nama = $mapUnitKerjaSub[$id] ?? '(tidak ditemukan)';
                                    return esc($id) . ' - ' . esc($nama);
                                }, $pjs);
                                echo implode('<br>', $namaPJ);
                            } else {
                                echo '<span class="text-gray-400 italic">Belum ditentukan</span>';
                            }
                            ?>
                        </td>


                        <td class="px-6 py-3 text-center space-x-2">
                            <button class="edit-btn text-blue-600 hover:underline" data-id="<?= esc($kat['id_kategori']) ?>">Edit</button>
                            <a href="<?= base_url('master/kategori/delete/' . $kat['id_kategori']) ?>"
                                class="delete-btn text-red-600 hover:underline cursor-pointer">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <button id="closeModal" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-3xl font-bold leading-none">&times;</button>
        <h3 class="text-xl font-semibold mb-4 text-blue-900 select-none">Edit Kategori</h3>

        <form id="editForm">
            <?= csrf_field() ?>
            <input type="hidden" name="id_kategori" id="edit_id_kategori" />

            <label for="edit_nama_kategori" class="block font-semibold mb-1">Nama Kategori</label>
            <input type="text" name="nama_kategori" id="edit_nama_kategori" class="w-full border rounded px-3 py-2 mb-4" required />

            <label for="edit_penanggung_jawab" class="block font-semibold mb-1">Penanggung Jawab</label>
            <select multiple name="penanggung_jawab[]" id="edit_penanggung_jawab" class="w-full border rounded px-3 py-2 mb-4">
                <option value="F38">F38 - IT Operation & Infrastructure</option>
                <option value="F39">F39 - IT Solution & Development</option>
                <option value="F42">F42 - HC Policy</option>
                <option value="F43">F43 - HC Development</option>
            </select>
            <small class="text-gray-500">Tekan Ctrl (atau Cmd di Mac) untuk pilih lebih dari satu</small>

            <div class="flex justify-end space-x-3">
                <button type="button" id="cancelBtn" class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100 transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 transition">Simpan</button>
            </div>
        </form>

        <div id="editErrors" class="mt-4 text-red-600 text-sm"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    $(document).ready(function() {
        // Buka modal dan isi data edit
        $('.edit-btn').on('click', function() {
            let id = $(this).data('id');
            $('#editErrors').html('');
            $.ajax({
                url: `<?= base_url('master/kategori/edit') ?>/${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    $('#edit_id_kategori').val(res.id_kategori);
                    $('#edit_nama_kategori').val(res.nama_kategori);
                    $('#editModal').removeClass('hidden');
                    if (res.penanggung_jawab) {
                        $('#edit_penanggung_jawab option').prop('selected', false); // reset
                        let selected = JSON.parse(res.penanggung_jawab);
                        selected.forEach(function(val) {
                            $(`#edit_penanggung_jawab option[value="${val}"]`).prop('selected', true);
                        });
                    }
                },
                error: function() {
                    alert('Gagal mengambil data kategori.');
                }
            });
        });

        // Tutup modal
        $('#closeModal, #cancelBtn').on('click', function() {
            $('#editModal').addClass('hidden');
            $('#editErrors').html('');
        });

        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            $('#editErrors').html('');
            let id = $('#edit_id_kategori').val();
            let namaKategori = $('#edit_nama_kategori').val();
            let csrfToken = $('input[name="<?= csrf_token() ?>"]').val();

            $.ajax({
                url: `<?= base_url('master/kategori/update') ?>/${id}`,
                method: 'POST',
                data: {
                    nama_kategori: namaKategori,
                    penanggung_jawab: $('#edit_penanggung_jawab').val(),
                    <?= csrf_token() ?>: csrfToken,
                },
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Kategori berhasil diupdate.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
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

    });

    $(document).ready(function() {
        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            Swal.fire({
                title: 'Yakin ingin hapus?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });

    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: "<?= session()->getFlashdata('success') ?>",
            timer: 2000,
            showConfirmButton: false
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>