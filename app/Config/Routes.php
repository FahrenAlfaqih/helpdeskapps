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
$routes->get('uploads/(:any)', 'FileServe::image/$1');



$routes->group('tickets', ['filter' => 'auth'], function ($routes) {
    $routes->get('', 'Tickets::index');               // halaman list tiket
    $routes->get('list', 'Tickets::list');            // API list tiket (bisa untuk DataTables)

    $routes->get('create', 'Tickets::createView');    // form buat tiket baru
    $routes->post('create', 'Tickets::create');       // simpan tiket baru

    $routes->get('list-for-unit', 'Tickets::listForUnit');  // API list tiket utk staf/kepala unit
    $routes->post('take', 'Tickets::takeTicket');
    $routes->post('finish', 'Tickets::finish');
    $routes->post('confirm', 'Tickets::confirmCompletion');


    $routes->get('board-staff', 'Tickets::boardStaffView');
    $routes->get('detail/(:num)', 'Tickets::detail/$1');
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
