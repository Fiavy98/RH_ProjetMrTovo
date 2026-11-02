<?php

/** Routes du squelette statique du Bloc 7 — Congés et absences. */
$routes->group('bloc7', ['namespace' => 'App\\Controllers\\Bloc7', 'filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Bloc7Controller::index');
    $routes->get('(:segment)', 'Bloc7Controller::show/$1');
});
