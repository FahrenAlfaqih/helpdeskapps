<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manajemen User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">

    <div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Manajemen User</h1>

        <form id="createUserForm" class="mb-6 space-y-4">
            <div>
                <label class="block mb-1 font-semibold">Username</label>
                <input type="text" id="username" class="border p-2 rounded w-full" required />
            </div>
            <div>
                <label class="block mb-1 font-semibold">Password</label>
                <input type="password" id="password" class="border p-2 rounded w-full" required />
            </div>
            <div>
                <label class="block mb-1 font-semibold">Nama Lengkap</label>
                <input type="text" id="full_name" class="border p-2 rounded w-full" required />
            </div>
            <div>
                <label class="block mb-1 font-semibold">Role</label>
                <select id="role_id" class="border p-2 rounded w-full" required>
                    <option value="">-- Pilih Role --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= esc($role['id']) ?>"><?= esc($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Buat User</button>
        </form>

        <div>
            <h2 class="text-xl font-semibold mb-3">Daftar User</h2>
            <div id="userList" class="space-y-2"></div>
        </div>
    </div>

    <script>
        async function loadUsers() {
            const res = await fetch('users/list');
            const users = await res.json();

            const list = document.getElementById('userList');
            list.innerHTML = '';

            if (users.length === 0) {
                list.innerHTML = '<p>Tidak ada user.</p>';
                return;
            }

            users.forEach(user => {
                const div = document.createElement('div');
                div.classList.add('flex', 'justify-between', 'items-center', 'border', 'p-3', 'rounded');

                div.innerHTML = `
        <div>
          <strong>${user.full_name}</strong> (${user.username}) - <em>${user.role_name ?? 'Unknown'}</em>
        </div>
        <button class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700" onclick="deleteUser(${user.id})">Hapus</button>
      `;
                list.appendChild(div);
            });
        }

        async function deleteUser(id) {
            if (!confirm('Yakin hapus user ini?')) return;

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
            if (result.status === 'success') {
                loadUsers();
            }
        }

        document.getElementById('createUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const full_name = document.getElementById('full_name').value.trim();
            const role_id = document.getElementById('role_id').value;

            if (!username || !password || !full_name || !role_id) {
                alert('Semua field wajib diisi');
                return;
            }

            const res = await fetch('users/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username,
                    password,
                    full_name,
                    role_id
                })
            });

            const result = await res.json();

            alert(result.message);
            if (result.status === 'success') {
                this.reset();
                loadUsers();
            }
        });

        loadUsers();
    </script>

</body>

</html>

<?= $this->endSection() ?>