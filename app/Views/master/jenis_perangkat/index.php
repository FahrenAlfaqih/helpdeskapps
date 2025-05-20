<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
  <h1 class="text-2xl font-bold mb-4 text-blue-900">Manajemen Jenis Perangkat</h1>

  <form id="addJenisForm" class="flex space-x-2 mb-4 items-center">
    <input type="text" id="jenisNama" placeholder="Nama Jenis Perangkat" required
      class="flex-1 border border-blue-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
    
    <!-- Dropdown kategori -->
    <select id="jenisKategori" required
      class="border border-blue-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
      <option value="">-- Pilih Kategori --</option>
      <option value="Hardware">Hardware</option>
      <option value="Software">Software</option>
      <option value="Jaringan">Jaringan</option>
      <option value="Lainnya">Lainnya</option>
    </select>

    <button type="submit" class="bg-blue-600 text-white px-4 rounded hover:bg-blue-700">Tambah</button>
  </form>

  <table class="w-full border border-blue-300 rounded">
    <thead class="bg-blue-100 text-blue-900">
      <tr>
        <th class="border border-blue-300 p-2 text-left">#</th>
        <th class="border border-blue-300 p-2 text-left">Nama Jenis Perangkat</th>
        <th class="border border-blue-300 p-2 text-left">Kategori</th>
        <th class="border border-blue-300 p-2 text-center">Aksi</th>
      </tr>
    </thead>
    <tbody id="jenisList"></tbody>
  </table>
</div>

<script>
  async function loadJenis(){
    const res = await fetch('/master/jenis-perangkat/list');
    const data = await res.json();
    const tbody = document.getElementById('jenisList');
    tbody.innerHTML = '';

    if(data.length === 0){
      tbody.innerHTML = '<tr><td colspan="4" class="text-center p-4">Belum ada data jenis perangkat.</td></tr>';
      return;
    }

    data.forEach((jenis, i) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="border border-blue-300 p-2">${i + 1}</td>
        <td class="border border-blue-300 p-2">${jenis.nama}</td>
        <td class="border border-blue-300 p-2">${jenis.kategori}</td>
        <td class="border border-blue-300 p-2 text-center">
          <button onclick="editJenis(${jenis.id}, '${jenis.nama}', '${jenis.kategori}')" class="text-blue-600 hover:underline mr-2">Edit</button>
          <button onclick="deleteJenis(${jenis.id})" class="text-red-600 hover:underline">Hapus</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  async function addJenis(e){
    e.preventDefault();
    const nama = document.getElementById('jenisNama').value.trim();
    const kategori = document.getElementById('jenisKategori').value;
    if(!nama) return alert('Nama jenis perangkat wajib diisi');
    if(!kategori) return alert('Kategori wajib dipilih');

    const res = await fetch('/master/jenis-perangkat/create', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `nama=${encodeURIComponent(nama)}&kategori=${encodeURIComponent(kategori)}`
    });
    const data = await res.json();
    alert(data.message);
    if(data.status === 'success'){
      document.getElementById('addJenisForm').reset();
      loadJenis();
    }
  }

  function editJenis(id, nama, kategori){
    const newNama = prompt('Edit Nama Jenis Perangkat:', nama);
    if(newNama === null) return;
    if(newNama.trim() === '') return alert('Nama tidak boleh kosong');

    const newKategori = prompt('Edit Kategori Jenis Perangkat (Hardware, Software, Jaringan, Lainnya):', kategori);
    if(newKategori === null) return;
    if(newKategori.trim() === '') return alert('Kategori tidak boleh kosong');

    fetch(`/master/jenis-perangkat/update/${id}`, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `nama=${encodeURIComponent(newNama.trim())}&kategori=${encodeURIComponent(newKategori.trim())}`
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if(data.status === 'success'){
        loadJenis();
      }
    });
  }

  function deleteJenis(id){
    if(!confirm('Yakin ingin menghapus jenis perangkat ini?')) return;
    fetch(`/master/jenis-perangkat/delete/${id}`, {method: 'POST'})
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        if(data.status === 'success'){
          loadJenis();
        }
      });
  }

  document.getElementById('addJenisForm').addEventListener('submit', addJenis);

  loadJenis();
</script>

<?= $this->endSection() ?>
