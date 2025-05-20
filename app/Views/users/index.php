<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold text-blue-900 mb-4">Manajemen User</h1>

    <div class="flex space-x-2 mb-4 items-center">
        <input
            type="text"
            id="searchInput"
            placeholder="Cari nama lengkap atau username..."
            class="flex-grow border border-blue-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
        <a href="<?= base_url('users/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-semibold whitespace-nowrap">
            + Tambah User
        </a>
    </div>




    <table class="w-full border border-blue-300 rounded">
        <thead class="bg-blue-100 text-blue-900">
            <tr>
                <th class="border border-blue-300 p-2 text-left">#</th>
                <th class="border border-blue-300 p-2 text-left">Nama Lengkap</th>
                <th class="border border-blue-300 p-2 text-left">Username</th>
                <th class="border border-blue-300 p-2 text-left">Email</th>
                <th class="border border-blue-300 p-2 text-left">Role</th>
                <th class="border border-blue-300 p-2 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody id="userTableBody" class="text-gray-700"></tbody>
    </table>

    <!-- Modal Edit -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
            <h2 class="text-xl font-semibold text-blue-700 mb-4">Edit User</h2>
            <form id="editUserForm" class="space-y-4">
                <input type="hidden" id="edit_id" />
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" id="edit_full_name" class="w-full border px-3 py-2 rounded" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="text" id="edit_email" class="w-full border px-3 py-2 rounded" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="edit_role_id" class="w-full border px-3 py-2 rounded" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>"><?= esc($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex justify-between mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-600 hover:underline">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('searchInput').addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#userTableBody tr');

        rows.forEach(row => {
            const fullName = row.cells[1].textContent.toLowerCase();
            const username = row.cells[2].textContent.toLowerCase();
            if (fullName.includes(filter) || username.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    async function loadUsers() {
        const res = await fetch('users/list');
        const users = await res.json();
        const tbody = document.getElementById('userTableBody');
        tbody.innerHTML = '';

        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center p-4 text-gray-500">Belum ada data user.</td></tr>';
            return;
        }

        users.forEach((user, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td class="border border-blue-300 p-2 text-center">${i + 1}</td>
        <td class="border border-blue-300 p-2">${user.full_name}</td>
        <td class="border border-blue-300 p-2">${user.username}</td>
        <td class="border border-blue-300 p-2">${user.email ?? '-'}</td>
        <td class="border border-blue-300 p-2">${user.role_name}</td>
        <td class="border border-blue-300 p-2 text-center space-x-2">
          <button onclick='openEditModal(${JSON.stringify(user)})' class="text-blue-600 hover:underline">Edit</button>
          <button onclick="deleteUser(${user.id})" class="text-red-600 hover:underline">Hapus</button>
        </td>
      `;
            tbody.appendChild(tr);
        });
    }

    function openEditModal(user) {
        document.getElementById('edit_id').value = user.id;
        document.getElementById('edit_full_name').value = user.full_name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role_id').value = user.role_id;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    async function deleteUser(id) {
        if (!confirm('Yakin ingin menghapus user ini?')) return;
        const res = await fetch('users/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id
            })
        });
        const result = await res.json();
        alert(result.message);
        if (result.status === 'success') loadUsers();
    }

    document.getElementById('editUserForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('edit_id').value;
        const full_name = document.getElementById('edit_full_name').value.trim();
        const email = document.getElementById('edit_email').value.trim();
        const role_id = document.getElementById('edit_role_id').value;

        const res = await fetch('users/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id,
                full_name,
                email,
                role_id
            })
        });
        const result = await res.json();
        alert(result.message);
        if (result.status === 'success') {
            closeModal();
            loadUsers();
        }
    });

    loadUsers();
</script>

<?= $this->endSection() ?>