<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h2 class="text-2xl font-semibold mb-6">Tiket untuk Unit Saya</h2>

<table id="ticketsTable" class="min-w-full border border-gray-300 rounded">
    <thead>
        <tr class="bg-gray-200 text-left">
            <th class="px-4 py-2 border-b">ID Tiket</th>
            <th class="px-4 py-2 border-b">Judul</th>
            <th class="px-4 py-2 border-b">Prioritas</th>
            <th class="px-4 py-2 border-b">Status</th>
            <th class="px-4 py-2 border-b">Aksi</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<!-- Modal Ambil Tiket -->
<div id="takeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Ambil Tiket</h3>
        <form id="takeForm" class="space-y-4">
            <input type="hidden" id="take_id_tiket" name="id_tiket" />
            <label class="block font-semibold">Komentar Penyelesaian</label>
            <textarea id="take_komentar" name="komentar_penyelesaian" class="w-full border rounded p-2" rows="3"></textarea>

            <label class="block font-semibold">Status</label>
            <select id="take_status" name="status" class="w-full border rounded p-2">
                <option value="In Progress">In Progress</option>
                <option value="Done">Done</option>
            </select>

            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" id="cancelTake" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Batal</button>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet" />
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable dengan ajax source dari list-for-unit
    var table = $('#ticketsTable').DataTable({
        ajax: "<?= base_url('tickets/list-for-unit') ?>",
        columns: [
            { data: 'id_tiket' },
            { data: 'judul' },
            { data: 'prioritas' },
            { data: 'status' },
            {
                data: null,
                render: function(data, type, row) {
                    if(row.status === 'Open' || row.status === 'In Progress') {
                        return `<button class="takeBtn bg-green-600 text-white px-3 py-1 rounded" data-id="${row.id_tiket}">Ambil Tiket</button>`;
                    }
                    return '-';
                }
            }
        ]
    });

    // Show modal on Ambil Tiket click
    $('#ticketsTable tbody').on('click', '.takeBtn', function() {
        var id = $(this).data('id');
        $('#take_id_tiket').val(id);
        $('#take_komentar').val('');
        $('#take_status').val('In Progress');
        $('#takeModal').removeClass('hidden');
    });

    // Cancel modal
    $('#cancelTake').click(function() {
        $('#takeModal').addClass('hidden');
    });

    // Submit ambil tiket form via AJAX
    $('#takeForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: "<?= base_url('tickets/take') ?>",
            method: "POST",
            data: formData,
            success: function(res) {
                if(res.status === 'success') {
                    alert(res.message);
                    $('#takeModal').addClass('hidden');
                    table.ajax.reload();
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function() {
                alert('Gagal menghubungi server');
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
