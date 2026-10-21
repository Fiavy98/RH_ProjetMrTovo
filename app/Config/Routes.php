<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * Ce fichier ne change presque jamais : chaque bloc déclare ses routes dans SON fichier
 * (app/Config/Routes/blocN.php), ce qui évite les conflits Git entre équipiers.
 */
$routes->get('/', 'Home::index');

// Outils de développement (désactivés en production)
if (ENVIRONMENT !== 'production') {
    $routes->get('dev/db', 'Dev::db');
    $routes->post('dev/role', 'Dev::role');
}

// Routes de chaque bloc
foreach ([1, 2, 3, 4] as $n) {
    require APPPATH . "Config/Routes/bloc{$n}.php";
}
