<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= isset($title) ? esc($title) : 'Help Desk System' ?></title>

    <!-- Load Tailwind CSS dulu -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Load plugin Typography CSS (bisa langsung CSSnya saja) -->
    <link href="https://cdn.jsdelivr.net/npm/@tailwindcss/typography@0.5.9/dist/typography.min.css" rel="stylesheet">



    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" />
    <style>
        .prose ul {
            list-style-type: disc;
            padding-left: 1.25rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
            list-style-position: outside;
            /* lebih umum agar bullet berada di luar teks */
        }

        .prose ol {
            list-style-type: decimal;
            padding-left: 1.25rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
            list-style-position: outside;
        }

        .prose ul li,
        .prose ol li {
            margin-bottom: 0.25rem;
        }
    </style>
</head>


<body class="bg-white text-blue-900 flex h-screen font-sans leading-relaxed">

    <!-- Sidebar -->
    <?php $uri = service('uri'); ?>
    <?php $unitLevelId = session()->get('unit_level_id'); ?>
    <?php $unitKerjaId = session()->get('unit_kerja_id'); ?>


    <aside
        class="w-64 bg-white shadow-md fixed top-4 left-4 h-[calc(100vh-2rem)] flex flex-col rounded-lg p-5 overflow-y-auto scrollbar-thin scrollbar-thumb-blue-400 scrollbar-track-blue-100">
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

            <?php if ($unitKerjaId === 'E13' || $unitKerjaId === 'E21'): ?>
                <a href="/tickets/board-staff"
                    class="block py-2 px-4 rounded-lg transition duration-200 ease-in-out
               <?= $uri->getSegment(1) == 'tickets/board-staff' && $uri->getSegment(2) == '' ? 'text-blue-600 bg-blue-50 font-semibold' : 'hover:bg-blue-100 hover:text-blue-700' ?>">
                    Tickets Board
                </a>
            <?php endif; ?>

            <?php if ($unitLevelId === 'A13' || $unitLevelId === 'A7' || $unitLevelId === 'A8'): ?>
                <a href="/master/kategori"
                    class="block py-2 px-4 rounded-lg transition duration-200 ease-in-out
            <?= $uri->getSegment(1) == 'master' && $uri->getSegment(2) == 'kategori' ? 'text-blue-600 bg-blue-50 font-semibold' : 'hover:bg-blue-100 hover:text-blue-700' ?>">
                    Master Kategori
                </a>
                <a href="/master/subkategori"
                    class="block py-2 px-4 rounded-lg transition duration-200 ease-in-out
            <?= $uri->getSegment(1) == 'master' && $uri->getSegment(2) == 'subkategori' ? 'text-blue-600 bg-blue-50 font-semibold' : 'hover:bg-blue-100 hover:text-blue-700' ?>">
                    Master Sub Kategori
                </a>
            <?php endif; ?>

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
    <div class="flex-1 flex flex-col overflow-auto" style="margin-left: 280px;">
        <header
            class="sticky top-4 z-20 bg-white border border-blue-200 rounded-lg shadow-md px-8 py-5 flex items-center justify-between mx-6"
            style="line-height: 1.5;">
            <h1 class="text-lg font-semibold select-none cursor-default tracking-wide">PT. Bakti Timah Medika</h1>

            <!-- Container kanan untuk nama user + role + avatar -->
            <div class="flex items-center space-x-4">
                <div class="text-blue-800 select-none cursor-default text-right">
                    <div class="font-medium px-5 py-1">
                        <?= esc(session()->get('nama') ?? 'User') ?>
                    </div>
                    <div class="text-sm text-blue-600 px-5">
                        <?= esc(session()->get('unit_level_name') ?? '-') ?> | <?= esc(session()->get('unit_usaha') ?? '-') ?> - <?= esc(session()->get('unit_kerja') ?? '-') ?>
                    </div>
                </div>

                <button
                    class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold uppercase select-none cursor-pointer">
                    <?= strtoupper(substr(session()->get('full_name') ?? 'U', 0, 1)) ?>
                </button>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 p-6 overflow-auto bg-white mx-6 my-4 rounded-lg ">
            <?= $this->renderSection('content') ?>
        </main>
    </div>


</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

</html>