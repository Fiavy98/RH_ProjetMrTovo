<?php

$routes->group('bloc6', ['namespace' => 'App\\Controllers\\Bloc6', 'filter' => 'auth'], static function ($routes) {
    $routes->get('anomalies', 'AnomaliesController::index', ['filter' => 'partied']);
    $routes->post('anomalies/detecter', 'AnomaliesController::detecter', ['filter' => ['partied', 'csrf']]);
    $routes->get('anomalies/(:num)', 'AnomaliesController::detail/$1', ['filter' => 'partied']);
    $routes->post('anomalies/(:num)/traiter', 'AnomaliesController::traiter/$1', ['filter' => ['partied', 'csrf']]);
    $routes->get('temps-travaille', 'TempsTravailleController::index', ['filter' => 'partied']);
});

$routes->group('bloc7', ['namespace' => 'App\\Controllers\\Bloc7', 'filter' => 'auth'], static function ($routes) {
    $routes->get('absences', 'AbsencesController::index', ['filter' => 'partied']);
    $routes->get('absences/nouveau', 'AbsencesController::form', ['filter' => 'partied']);
    $routes->post('absences/enregistrer', 'AbsencesController::save', ['filter' => ['partied', 'csrf']]);
    $routes->get('absences/(:num)', 'AbsencesController::detail/$1', ['filter' => 'partied']);
    $routes->get('absences/(:num)/modifier', 'AbsencesController::form/$1', ['filter' => 'partied']);
    $routes->post('absences/(:num)/enregistrer', 'AbsencesController::save/$1', ['filter' => ['partied', 'csrf']]);
    $routes->get('absences/(:num)/justificatif', 'AbsencesController::justificatif/$1', ['filter' => 'partied']);
    $routes->get('types-conges', 'TypesCongesController::index', ['filter' => 'partied']);
    $routes->get('types-conges/nouveau', 'TypesCongesController::form', ['filter' => 'partied']);
    $routes->post('types-conges/enregistrer', 'TypesCongesController::save', ['filter' => ['partied', 'csrf']]);
    $routes->get('types-conges/(:num)/modifier', 'TypesCongesController::form/$1', ['filter' => 'partied']);
    $routes->post('types-conges/(:num)/enregistrer', 'TypesCongesController::save/$1', ['filter' => ['partied', 'csrf']]);
    $routes->post('types-conges/(:num)/desactiver', 'TypesCongesController::disable/$1', ['filter' => ['partied', 'csrf']]);
});
