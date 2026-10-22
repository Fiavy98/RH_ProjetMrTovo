<?php

/** Routes du Bloc 1 : expression du besoin. */
$routes->group('bloc1', ['namespace' => 'App\\Controllers\\Bloc1'], static function ($routes) {
    $routes->get('/', 'Bloc1Controller::index');
    $routes->get('rh-review/(:num)', 'Bloc1Controller::rhDetail/$1');
    $routes->post('rh-review/(:num)', 'Bloc1Controller::rhDecision/$1');
    $routes->get('finance-review/(:num)', 'Bloc1Controller::financeDetail/$1');
    $routes->post('finance-review/(:num)', 'Bloc1Controller::financeDecision/$1');
    $routes->get('direction-review', 'Bloc1Controller::directionList');
    $routes->get('direction-review/(:num)', 'Bloc1Controller::directionDetail/$1');
    $routes->post('direction-review/(:num)', 'Bloc1Controller::directionDecision/$1');
    $routes->post('direction-review/(:num)/post', 'Bloc1Controller::createPost/$1');
    $routes->get('history', 'Bloc1Controller::historyList');
    $routes->get('history/(:num)', 'Bloc1Controller::historyDetail/$1');
    $routes->get('(:segment)', 'Bloc1Controller::show/$1');
    $routes->post('(:segment)', 'Bloc1Controller::save/$1');
});
