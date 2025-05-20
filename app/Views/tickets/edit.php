<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
  <h2 class="text-2xl font-semibold mb-6 text-blue-900">Update Tiket</h2>

  <form id="updateTicketForm" class="space-y-6">
    <input type="hidden" id="ticket_id" name="ticket_id" value="<?= esc($ticket['id']) ?>" />

    <?= view('components/input', [
      'label' => 'Judul Tiket',
      'type' => 'text',
      'id' => 'title',
      'name' => 'title',
      'value' => $ticket['title'],
      'readonly' => true,
    ]) ?>

    <?= view('components/input', [
      'label' => 'Status',
      'type' => 'select',
      'id' => 'status',
      'name' => 'status',
      'options' => [
        'Menunggu' => 'Menunggu',
        'Open' => 'Open',
        'In Progress' => 'In Progress',
        'Done' => 'Done',
        'Selesai' => 'Selesai',
        'Belum Selesai' => 'Belum Selesai'
      ],
      'value' => $ticket['status'],
      'required' => true
    ]) ?>

    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition font-semibold">
      Simpan Perubahan
    </button>
  </form>
</div>

<script>
document.getElementById('updateTicketForm').addEventListener('submit', async function(e){
  e.preventDefault();

  const ticket_id = this.ticket_id.value;
  const status = this.status.value;

  if(!status){
    alert('Status wajib diisi');
    return;
  }

  const res = await fetch('/tickets/update-status', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ ticket_id, status })
  });
  
  const data = await res.json();
  alert(data.message);
  
  if(data.status === 'success'){
    window.location.href = '/tickets';
  }
});
</script>

<?= $this->endSection() ?>
