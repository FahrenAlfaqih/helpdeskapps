<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-8 rounded-2xl shadow-xl">
    <h1 class="text-2xl font-bold text-blue-800 mb-6">Tambah User Baru</h1>

    <form id="createUserForm" class="space-y-6">
        <?= view('components/input', [
            'label' => 'Username',
            'id' => 'username',
            'name' => 'username',
            'required' => true,
            'placeholder' => 'Masukkan username'
        ]) ?>

        <?= view('components/input', [
            'label' => 'Nama Lengkap',
            'id' => 'full_name',
            'name' => 'full_name',
            'required' => true,
            'placeholder' => 'Masukkan nama lengkap'
        ]) ?>

        <?= view('components/input', [
            'type' => 'email',
            'label' => 'Email',
            'id' => 'email',
            'name' => 'email',
            'required' => true,
            'placeholder' => 'Masukkan Email Valid'
        ]) ?>

        <?= view('components/input', [
            'label' => 'Role',
            'id' => 'role_id',
            'name' => 'role_id',
            'type' => 'select',
            'required' => true,
            'options' => array_column($roles, 'name', 'id')
        ]) ?>

        <?= view('components/input', [
            'label' => 'Password',
            'id' => 'password',
            'name' => 'password',
            'type' => 'password',
            'required' => true,
            'placeholder' => 'Masukkan password'
        ]) ?>



        <div class="flex justify-between items-center">
            <a href="<?= base_url('users') ?>" class="text-blue-600 hover:underline">← Kembali ke daftar user</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">Simpan</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('createUserForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        const full_name = document.getElementById('full_name').value.trim();
        const email = document.getElementById('email').value.trim();
        const role_id = document.getElementById('role_id').value;

        if (!username || !password || !full_name || !email|| !role_id) {
            alert('Semua field wajib diisi');
            return;
        }

        const res = await fetch('<?= base_url('users/create') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username,
                password,
                full_name,
                email,
                role_id
            })
        });

        const result = await res.json();
        alert(result.message);

        if (result.status === 'success') {
            window.location.href = '<?= base_url('users') ?>';
        }
    });
</script>

<?= $this->endSection() ?>