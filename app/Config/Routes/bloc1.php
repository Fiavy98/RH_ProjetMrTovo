<?php

/** Routes du Bloc 1 : expression du besoin. */
$routes->group('bloc1', ['namespace' => 'App\\Controllers\\Bloc1'], static function ($routes) {
    $routes->get('/', 'Bloc1Controller::index');
    $routes->get('rh-review/(:num)', 'RhController::detail/$1');
    $routes->post('rh-review/(:num)', 'RhController::decision/$1');
    $routes->get('finance-review/(:num)', 'FinanceController::detail/$1');
    $routes->post('finance-review/(:num)', 'FinanceController::decision/$1');
    $routes->get('direction-review', 'DirectionController::list');
    $routes->get('direction-review/(:num)', 'DirectionController::detail/$1');
    $routes->post('direction-review/(:num)', 'DirectionController::decision/$1');
    $routes->post('direction-review/(:num)/post', 'DirectionController::createPost/$1');
    $routes->get('history', 'HistoryController::list');
    $routes->get('history/(:num)', 'HistoryController::detail/$1');
    $routes->get('(:segment)', 'Bloc1Controller::show/$1');
    $routes->post('(:segment)', 'Bloc1Controller::save/$1');
});
