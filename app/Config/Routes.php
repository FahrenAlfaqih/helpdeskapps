<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', function () {
    echo view('login');
});

$routes->post('auth/login', 'Auth::login');
$routes->get('auth/logout', 'Auth::logout');

// Dashboard
// $routes->get('dashboard', function () {
//     if (!session()->get('logged_in')) {
//         return redirect('/');
//     }
//     return view('dashboard');
// }, ['filter' => 'auth']);
// Jadi ini
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);


// Users management group (Admin & Kepala Unit)
$routes->group('users', ['filter' => 'auth'], function ($routes) {
    $routes->get('', 'Users::index');             // daftar user
    $routes->get('create', 'Users::create');      // form buat user
    $routes->get('list', 'Users::listUsers');
    $routes->post('create', 'Users::createUser');
    $routes->post('update', 'Users::updateUser');
    $routes->post('delete', 'Users::deleteUser');
});


$routes->group('tickets', ['filter' => 'auth'], function ($routes) {
    $routes->get('', 'Tickets::index');              // /tickets
    $routes->get('list', 'Tickets::list');           // API list tiket
    $routes->get('create', 'Tickets::createView');   // halaman buat tiket baru
    $routes->post('create', 'Tickets::create');      // API create tiket
    $routes->get('update/(:num)', 'Tickets::updateView/$1');   // halaman update tiket
    $routes->get('detail/(:num)', 'Tickets::detailView/$1');   // halaman detail tiket
    $routes->get('api-detail/(:num)', 'Tickets::apiDetail/$1'); // API detail tiket
    $routes->post('update-status', 'Tickets::updateStatus');

    $routes->get('staff/unit/(:segment)', 'Tickets::getStaffByUnit/$1');
    $routes->post('assign', 'Tickets::assign');

    $routes->get('board-staff', 'Tickets::boardStaffView');
    $routes->get('staff/count/(:segment)', 'Tickets::countByStatus/$1');


    $routes->get('staff/list/(:segment)', 'Tickets::staffList/$1');
    $routes->post('take', 'Tickets::takeTicket');
    $routes->post('transfer', 'Tickets::transferTicket');
    $routes->post('transfer/respond', 'Tickets::respondTransfer');
    $routes->get('transfer/list', 'Tickets::listTransfers');

    $routes->post('saveFeedback', 'Tickets::saveFeedback');
    $routes->post('saveComment', 'Tickets::saveComment');


    //Khusus Kepala unit
    $routes->get('board', 'Tickets::boardView');
    $routes->get('board/list/(:segment)', 'Tickets::boardList/$1');
});

$routes->group('master', ['filter' => 'auth'], function ($routes) {
    // Ruangan
    $routes->get('ruangan', 'MasterRuangan::index');           // halaman list ruangan
    $routes->get('ruangan/list', 'MasterRuangan::list');       // API list ruangan (json)
    $routes->post('ruangan/create', 'MasterRuangan::create');  // API tambah ruangan
    $routes->post('ruangan/update/(:num)', 'MasterRuangan::update/$1');  // API update ruangan
    $routes->post('ruangan/delete/(:num)', 'MasterRuangan::delete/$1');  // API hapus ruangan

    // Jenis Perangkat
    $routes->get('jenis-perangkat', 'MasterJenisPerangkat::index');
    $routes->get('jenis-perangkat/list', 'MasterJenisPerangkat::list');
    $routes->get('jenis-perangkat/by-kategori/(:segment)', 'MasterJenisPerangkat::listByKategori/$1');
    $routes->post('jenis-perangkat/create', 'MasterJenisPerangkat::create');
    $routes->post('jenis-perangkat/update/(:num)', 'MasterJenisPerangkat::update/$1');
    $routes->post('jenis-perangkat/delete/(:num)', 'MasterJenisPerangkat::delete/$1');
});


$routes->get('email/test', 'EmailSender::sendTestEmail');
