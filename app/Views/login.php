<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Helpdesk - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="h-full flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-blue-700 tracking-wide">Helpdesk</h1>
            <!-- <p class="mt-2 text-gray-600 font-medium">Masuk ke sistem Anda</p> -->
            <!-- <p class="mt-2 text-gray-600 font-light text-md">Pastikan akun anda telah terdaftar sebelumnya baru melakukan login ke dalam sistem!</p> -->

        </div>
        <form id="loginForm" class="mt-8 space-y-6">
            <div class="rounded-md shadow-sm -space-y-px">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
                    <input id="username" name="username" type="text" autocomplete="username" required
                        class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" />
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" />
                </div>
            </div>

            <div id="message" class="text-red-600 text-sm mt-2"></div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Masuk
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const messageEl = document.getElementById('message');
            messageEl.textContent = '';

            if (!username || !password) {
                messageEl.textContent = 'Username dan password wajib diisi';
                return;
            }

            try {
                const response = await fetch('/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username,
                        password
                    })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    window.location.href = '/dashboard'; // redirect ke dashboard kalau sukses
                } else {
                    messageEl.textContent = result.message;
                }
            } catch (error) {
                messageEl.textContent = 'Terjadi kesalahan saat login. Coba lagi.';
            }
        });
    </script>
</body>

</html>