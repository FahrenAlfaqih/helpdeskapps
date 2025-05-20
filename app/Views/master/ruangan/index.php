<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
  <h1 class="text-2xl font-bold mb-4 text-blue-900">Manajemen Ruangan</h1>

  <form id="addRuanganForm" class="flex space-x-2 mb-4">
    <input type="text" id="ruanganNama" placeholder="Nama Ruangan" required
      class="flex-1 border border-blue-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
    <button type="submit" class="bg-blue-600 text-white px-4 rounded hover:bg-blue-700">Tambah</button>
  </form>

  <table class="w-full border border-blue-300 rounded">
    <thead class="bg-blue-100 text-blue-900">
      <tr>
        <th class="border border-blue-300 p-2 text-left">#</th>
        <th class="border border-blue-300 p-2 text-left">Nama Ruangan</th>
        <th class="border border-blue-300 p-2 text-center">Aksi</th>
      </tr>
    </thead>
    <tbody id="ruanganList"></tbody>
  </table>
</div>

<script>
  async function loadRuangan(){
    const res = await fetch('/master/ruangan/list');
    const data = await res.json();
    const tbody = document.getElementById('ruanganList');
    tbody.innerHTML = '';

    if(data.length === 0){
      tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4">Belum ada data ruangan.</td></tr>';
      return;
    }

    data.forEach((ruangan, i) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="border border-blue-300 p-2">${i + 1}</td>
        <td class="border border-blue-300 p-2">${ruangan.nama}</td>
        <td class="border border-blue-300 p-2 text-center">
          <button onclick="editRuangan(${ruangan.id}, '${ruangan.nama}')" class="text-blue-600 hover:underline mr-2">Edit</button>
          <button onclick="deleteRuangan(${ruangan.id})" class="text-red-600 hover:underline">Hapus</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  async function addRuangan(e){
    e.preventDefault();
    const nama = document.getElementById('ruanganNama').value.trim();
    if(!nama) return alert('Nama ruangan wajib diisi');

    const res = await fetch('/master/ruangan/create', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `nama=${encodeURIComponent(nama)}`
    });
    const data = await res.json();
    alert(data.message);
    if(data.status === 'success'){
      document.getElementById('addRuanganForm').reset();
      loadRuangan();
    }
  }

  function editRuangan(id, nama){
    const newNama = prompt('Edit Nama Ruangan:', nama);
    if(newNama === null) return;
    if(newNama.trim() === '') return alert('Nama tidak boleh kosong');

    fetch(`/master/ruangan/update/${id}`, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `nama=${encodeURIComponent(newNama.trim())}`
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if(data.status === 'success'){
        loadRuangan();
      }
    });
  }

  function deleteRuangan(id){
    if(!confirm('Yakin ingin menghapus ruangan ini?')) return;
    fetch(`/master/ruangan/delete/${id}`, {method: 'POST'})
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        if(data.status === 'success'){
          loadRuangan();
        }
      });
  }

  document.getElementById('addRuanganForm').addEventListener('submit', addRuangan);

  loadRuangan();
</script>

<?= $this->endSection() ?>
