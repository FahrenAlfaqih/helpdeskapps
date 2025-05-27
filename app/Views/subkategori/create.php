<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-3xl font-bold mb-8 text-blue-900 border-b border-blue-300 pb-2 select-none">Tambah Sub Kategori Baru</h2>

    <form id="createSubKategoriForm" class="space-y-6">
        <?= csrf_field() ?>

        <div>
            <label for="id_kategori" class="block font-semibold mb-1">Pilih Kategori</label>
            <select id="id_kategori" name="id_kategori" required
                    class="w-full border rounded px-3 py-2">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach($kategori as $kat): ?>
                    <option value="<?= esc($kat['id_kategori']) ?>"><?= esc($kat['nama_kategori']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="nama_subkategori" class="block font-semibold mb-1">Nama Sub Kategori</label>
            <input type="text" id="nama_subkategori" name="nama_subkategori" required
                   class="w-full border rounded px-3 py-2" />
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
            Simpan Sub Kategori
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('#createSubKategoriForm').on('submit', function(e) {
        e.preventDefault();

        let form = $(this);
        let url = "<?= base_url('master/subkategori/store') ?>";
        let formData = form.serialize();

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message || 'Sub Kategori berhasil dibuat!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Setelah OK, redirect ke list subkategori
                        window.location.href = "<?= base_url('master/subkategori') ?>";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Terjadi kesalahan saat membuat Sub Kategori.',
                    });
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors || {};
                let errorMessages = Object.values(errors).flat().join('\n') || 'Terjadi kesalahan.';
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: errorMessages,
                });
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
