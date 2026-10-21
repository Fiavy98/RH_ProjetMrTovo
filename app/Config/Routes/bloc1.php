<?php

/** Routes du Bloc 1 : expression du besoin. */
$routes->group('bloc1', ['namespace' => 'App\\Controllers\\Bloc1'], static function ($routes) {
    $routes->get('/', 'Bloc1Controller::index');
    $routes->get('(:segment)', 'Bloc1Controller::show/$1');
    $routes->post('(:segment)', 'Bloc1Controller::save/$1');
});
