<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-3xl font-bold mb-8 text-blue-900 border-b border-blue-300 pb-2 select-none">Tambah Kategori Baru</h2>

    <div id="errorContainer" class="hidden mb-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded shadow-sm"></div>

    <form id="createKategoriForm" class="space-y-6">
        <?= csrf_field() ?>

        <div>
            <label for="nama_kategori" class="block font-semibold mb-1">Nama Kategori</label>
            <input type="text" id="nama_kategori" name="nama_kategori" required
                class="w-full border rounded px-3 py-2" />
        </div>

        <div>
            <label for="penanggung_jawab" class="block font-semibold mb-1">Penanggung Jawab</label>
            <select multiple name="penanggung_jawab[]" id="penanggung_jawab" class="w-full border rounded px-3 py-2">
                <option value="F38">F38 - IT Operation & Infrastructure</option>
                <option value="F39">F39 - IT Solution & Development</option>
                <option value="F42">F42 - HC Policy</option>
                <option value="F43">F43 - HC Development</option>
            </select>
            <small class="text-gray-500">Tekan Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu.</small>
        </div>


        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
            Simpan Kategori
        </button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#createKategoriForm').on('submit', function(e) {
        e.preventDefault();

        $('#errorContainer').hide().html('');

        $.ajax({
            url: "<?= base_url('master/kategori/store') ?>",
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                if (res.status === 'success' || !res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Kategori berhasil dibuat',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "<?= base_url('master/kategori') ?>";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Terjadi kesalahan'
                    });
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON?.errors) {
                    let errors = xhr.responseJSON.errors;
                    let html = '<ul class="list-disc list-inside">';
                    Object.values(errors).forEach(function(errs) {
                        errs.forEach(function(e) {
                            html += `<li>${e}</li>`;
                        });
                    });
                    html += '</ul>';
                    $('#errorContainer').html(html).show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat mengirim data',
                    });
                }
            }
        });
    });
</script>

<?= $this->endSection() ?>