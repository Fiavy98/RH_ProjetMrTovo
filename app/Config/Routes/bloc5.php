<?php

/** Routes du Bloc 5 — Dossier administratif. */
$routes->group('bloc5', ['namespace' => 'App\\Controllers\\Bloc5', 'filter' =>  ['auth', 'internal']], static function ($routes) {
    $routes->get('/', 'DossiersController::index');
    $routes->get('dossiers', 'DossiersController::index');
    $routes->get('dossier', 'DossierController::index');
    $routes->get('dossier/(:num)', 'DossierController::show/$1');
    $routes->get('documents', 'DocumentsController::index');
    $routes->get('documents/(:num)', 'DocumentsController::index/$1');
    $routes->post('documents/(:num)/store', 'DocumentsController::store/$1');
    $routes->post('documents/(:num)/update/(:num)', 'DocumentsController::update/$1/$2');
    $routes->post('documents/(:num)/delete/(:num)', 'DocumentsController::delete/$1/$2');
    $routes->get('documents/(:num)/download/(:num)', 'DocumentsController::download/$1/$2');
    $routes->get('carriere', 'CarriereController::index');
    $routes->get('carriere/(:num)', 'CarriereController::index/$1');
    $routes->get('audit', 'AuditController::index');

    $routes->get('(:segment)', 'Bloc5Controller::show/$1');
});
