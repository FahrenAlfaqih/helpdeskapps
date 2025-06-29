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
$routes->get('api/kategori/penanggung-jawab/(:any)', 'Api\Kategori::penanggungJawab/$1');


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
    $routes->get('detail/(:segment)', 'Tickets::detail/$1');
});

$routes->group('master', function ($routes) {
    $routes->get('kategori', 'Kategori::index');
    $routes->get('kategori/create', 'Kategori::create');
    $routes->post('kategori/store', 'Kategori::store');
    $routes->get('kategori/edit/(:segment)', 'Kategori::edit/$1');
    $routes->post('kategori/update/(:segment)', 'Kategori::update/$1');
    $routes->get('kategori/delete/(:segment)', 'Kategori::delete/$1');

    $routes->get('subkategori', 'SubKategori::index');
    $routes->get('subkategori/create', 'SubKategori::create');
    $routes->post('subkategori/store', 'SubKategori::store');
    $routes->get('subkategori/edit/(:segment)', 'SubKategori::edit/$1');
    $routes->post('subkategori/update/(:segment)', 'SubKategori::update/$1');
    $routes->post('subkategori/delete/(:segment)', 'SubKategori::delete/$1');

    $routes->get('ruangan', 'Ruangan::index');
    $routes->get('ruangan/create', 'Ruangan::create');
    $routes->post('ruangan/store', 'Ruangan::store');
    $routes->get('ruangan/edit/(:segment)', 'Ruangan::edit/$1');
    $routes->post('ruangan/update/(:segment)', 'Ruangan::update/$1');
    $routes->get('ruangan/delete/(:segment)', 'Ruangan::delete/$1');
});


$routes->get('email/test', 'EmailSender::sendTestEmail');
