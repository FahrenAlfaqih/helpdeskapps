<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-semibold mb-6 text-blue-900">Tambah Tiket Baru</h2>
    <form id="addTicketForm" class="space-y-6">

        <div class="flex flex-col md:flex-row md:space-x-8">
            <!-- Kiri -->
            <div class="flex-1 space-y-4">
                <?= view('components/input', [
                    'label' => 'Nama',
                    'type' => 'text',
                    'id' => 'nama',
                    'name' => 'nama',
                    'placeholder' => 'Nama lengkap',
                    'value' => session()->get('full_name'),
                    'required' => true,
                    'readonly' => true
                ]) ?>

                <?= view('components/input', [
                    'label' => 'Tujuan',
                    'type' => 'select',
                    'id' => 'tujuan',
                    'name' => 'tujuan',
                    'options' => ['IT' => 'Team IT', 'GA' => 'General Affair'],
                    'required' => true
                ]) ?>

                <?= view('components/input', [
                    'label' => 'Kategori',
                    'type' => 'select',
                    'id' => 'kategori',
                    'name' => 'kategori',
                    'options' => $kategori_list,
                    'required' => true
                ]) ?>

            </div>

            <!-- Kanan -->
            <div class="flex-1 space-y-4 mt-6 md:mt-0">
                <?= view('components/input', [
                    'label' => 'Tanggal',
                    'type' => 'text',
                    'id' => 'tanggal',
                    'name' => 'tanggal',
                    'placeholder' => 'Pilih tanggal',
                    'required' => true,
                    'value' => ''
                ]) ?>


                <?= view('components/input', [
                    'label' => 'Ruangan',
                    'type' => 'select',
                    'id' => 'ruangan_id',
                    'name' => 'ruangan_id',
                    'options' => array_column($ruangan_list, 'nama', 'id'),
                    'required' => true
                ]) ?>

                <?= view('components/input', [
                    'label' => 'Jenis Perangkat',
                    'type' => 'select',
                    'id' => 'jenis_perangkat_id',
                    'name' => 'jenis_perangkat_id',
                    'options' => [],
                    'required' => true
                ]) ?>
            </div>
        </div>
        <?= view('components/input', [
            'label' => 'Permasalahan',
            'type' => 'textarea',
            'id' => 'title',
            'name' => 'title',
            'placeholder' => 'Permasalahan',
            'required' => true,
        ]) ?>

        <!-- Deskripsi dengan toolbar -->
        <div>
            <label for="deskripsi" class="block text-sm font-medium text-blue-700 mb-2">Deskripsi Permasalahan</label>

            <div class="mb-2 space-x-2">
                <button type="button" onclick="formatText('bold')" class="px-2 py-1 border rounded hover:bg-blue-100" title="Bold"><strong>B</strong></button>
                <button type="button" onclick="formatText('italic')" class="px-2 py-1 border rounded hover:bg-blue-100" title="Italic"><em>I</em></button>
                <button type="button" onclick="formatText('underline')" class="px-2 py-1 border rounded hover:bg-blue-100" title="Underline"><u>U</u></button>
                <button type="button" onclick="formatText('insertUnorderedList')" class="px-2 py-1 border rounded hover:bg-blue-100" title="List">&bull; List</button>
                <button type="button" onclick="formatText('insertOrderedList')" class="px-2 py-1 border rounded hover:bg-blue-100" title="Numbered List">1. List</button>
            </div>

            <div
                id="deskripsi"
                contenteditable="true"
                class="border border-blue-300 rounded p-3 min-h-[120px] focus:outline-none focus:ring-2 focus:ring-blue-400 overflow-y-auto"></div>

            <textarea name="deskripsi" id="deskripsi_input" class="hidden"></textarea>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition font-semibold mt-4">Kirim Tiket</button>
    </form>
</div>

<script>
    flatpickr("#tanggal", {
        dateFormat: "Y-m-d",
        allowInput: true,

    });

    function formatText(command) {
        const deskripsi = document.getElementById('deskripsi');
        deskripsi.focus();
        document.execCommand(command, false, null);
    }



    // Supaya textarea yang hidden sinkron dengan konten editable saat submit form
    const form = document.getElementById('addTicketForm') || document.getElementById('createTicketForm') || document.getElementById('updateTicketForm');

    if (form) {
        form.addEventListener('submit', e => {
            const deskripsiDiv = document.getElementById('deskripsi');
            const deskripsiInput = document.getElementById('deskripsi_input');
            if (deskripsiDiv && deskripsiInput) {
                deskripsiInput.value = deskripsiDiv.innerHTML.trim();
            }
        });
    }

    document.getElementById('kategori').addEventListener('change', async function() {
        const kategori = this.value;
        const jenisSelect = document.getElementById('jenis_perangkat_id');

        // Reset dulu dropdown jenis perangkat
        jenisSelect.innerHTML = '<option value="">Loading...</option>';
        jenisSelect.disabled = true;

        if (!kategori) {
            jenisSelect.innerHTML = '<option value="">-- Pilih Kategori dulu --</option>';
            jenisSelect.disabled = true;
            return;
        }

        try {
            const res = await fetch(`/master/jenis-perangkat/by-kategori/${encodeURIComponent(kategori)}`);
            if (!res.ok) throw new Error('Gagal load data jenis perangkat');
            const data = await res.json();

            if (data.length === 0) {
                jenisSelect.innerHTML = '<option value="">Tidak ada jenis perangkat untuk kategori ini</option>';
                jenisSelect.disabled = true;
                return;
            }

            // Isi dropdown dengan data yang diterima
            jenisSelect.innerHTML = '<option value="">-- Pilih Jenis Perangkat --</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama;
                jenisSelect.appendChild(option);
            });

            jenisSelect.disabled = false;
        } catch (error) {
            jenisSelect.innerHTML = '<option value="">Gagal memuat data</option>';
            jenisSelect.disabled = true;
            console.error(error);
        }
    });

    document.getElementById('addTicketForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        // Ambil nilai input
        const title = this.title.value.trim();
        const assigned_unit = this.tujuan.value;
        const kategori = this.kategori.value;
        const tanggal = this.tanggal.value;
        const ruangan_id = this.ruangan_id.value;
        const jenis_perangkat_id = this.jenis_perangkat_id.value;

        // Ambil konten dari contenteditable deskripsi
        const descriptionEditor = document.getElementById('deskripsi');
        const description = descriptionEditor.innerHTML.trim();

        // Validasi wajib
        if (!title || !assigned_unit || !kategori || !tanggal || !ruangan_id || !jenis_perangkat_id) {
            alert('Semua field wajib diisi kecuali deskripsi.');
            return;
        }

        // Validasi deskripsi sederhana (boleh kosong sesuai kebutuhan)
        if (!description || description === '<br>') {
            alert('Deskripsi wajib diisi');
            return;
        }

        // Prepare data JSON
        const payload = {
            title,
            assigned_unit,
            kategori,
            tanggal,
            ruangan_id,
            jenis_perangkat_id,
            description
        };

        // Kirim ke server
        const res = await fetch('/tickets/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        alert(data.message);
        if (data.status === 'success') {
            this.reset();
            descriptionEditor.innerHTML = ''; // reset editor
            window.location.href = '/tickets';
        }
    });
</script>

<?= $this->endSection() ?>