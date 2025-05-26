<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= isset($title) ? esc($title) : 'Help Desk System' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" />


</head>

<body class="bg-white text-blue-900 flex h-screen overflow-hidden font-sans leading-relaxed">

    <!-- Sidebar -->
    <?php $roleId = session()->get('role_id'); ?>
    <?php $uri = service('uri'); ?>

    <aside
        class="w-64 bg-white shadow-md sticky top-4 left-4 h-[calc(100vh-2rem)] flex flex-col rounded-lg p-5 overflow-y-auto scrollbar-thin scrollbar-thumb-blue-400 scrollbar-track-blue-100">
        <div class="mb-8 text-2xl font-semibold text-blue-800 select-none cursor-default tracking-wide">
            Help Desk
        </div>

        <nav class="flex-1 flex flex-col text-sm font-medium text-gray-500 bg-white">
            <!-- Dashboard -->
            <a href="/dashboard"
                class="block py-2 px-4 rounded-lg transition duration-200 ease-in-out
           <?= $uri->getSegment(1) == 'dashboard' ? 'text-blue-600 bg-blue-50 font-semibold' : 'hover:bg-blue-100 hover:text-blue-700' ?>">
                Dashboard
            </a>


            <a href="/tickets"
                class="block py-2 px-4 rounded-lg transition duration-200 ease-in-out
               <?= $uri->getSegment(1) == 'tickets' && $uri->getSegment(2) == '' ? 'text-blue-600 bg-blue-50 font-semibold' : 'hover:bg-blue-100 hover:text-blue-700' ?>">
                Tickets
            </a>
            <a href="/tickets/board-staff"
                class="block py-2 px-4 rounded-lg transition duration-200 ease-in-out
               <?= $uri->getSegment(1) == 'tickets/board-staff' && $uri->getSegment(2) == '' ? 'text-blue-600 bg-blue-50 font-semibold' : 'hover:bg-blue-100 hover:text-blue-700' ?>">
                Tickets Board
            </a>

            <!-- Spacer agar Logout selalu di bawah -->
            <div class="flex-1"></div>

            <!-- Logout Button -->
            <a href="/auth/logout"
                class="block py-3 px-4 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-center font-semibold transition duration-200 ease-in-out mt-4">
                Logout
            </a>
        </nav>
    </aside>


    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-auto">

        <header
            class="sticky top-4 left-0 right-0 z-20 bg-white border border-blue-200 rounded-lg shadow-md px-8 py-5 flex items-center justify-between mx-6"
            style="line-height: 1.5;">
            <h1 class="text-lg font-semibold select-none cursor-default tracking-wide">PT. Bakti Timah Medika</h1>

            <!-- Container kanan untuk nama user + role + avatar -->
            <div class="flex items-center space-x-4">
                <div class="text-blue-800 select-none cursor-default text-right">
                    <div class="font-medium px-5 py-1">
                        <?= esc(session()->get('nama') ?? 'User') ?>
                    </div>
                    <div class="text-sm text-blue-600 px-5">
                        <?= esc(session()->get('unit_usaha') ?? '-') ?> - <?= esc(session()->get('unit_kerja') ?? '-') ?>
                    </div>
                </div>

                <button
                    class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold uppercase select-none cursor-pointer">
                    <?= strtoupper(substr(session()->get('full_name') ?? 'U', 0, 1)) ?>
                </button>
            </div>
        </header>



        <!-- Content -->
        <main class="flex-1 p-6 overflow-auto bg-white mx-6 my-4 rounded-lg shadow-md">
            <?= $this->renderSection('content') ?>
        </main>
    </div>

</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

</html>