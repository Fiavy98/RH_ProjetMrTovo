<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * Ce fichier ne change presque jamais : chaque bloc déclare ses routes dans SON fichier
 * (app/Config/Routes/blocN.php), ce qui évite les conflits Git entre équipiers.
 */
$routes->get('/', 'Home::index', ['filter' => 'auth']);
$routes->get('auth', 'AuthController::index');
$routes->post('auth/select', 'AuthController::select');
$routes->post('auth/logout', 'AuthController::logout', ['filter' => 'auth']);

// Outils de développement (désactivés en production)
if (ENVIRONMENT !== 'production') {
    $routes->get('dev/db', 'Dev::db', ['filter' => 'auth']);
}

// Routes de chaque bloc
foreach ([1, 2, 3, 4] as $n) {
    require APPPATH . "Config/Routes/bloc{$n}.php";
}
