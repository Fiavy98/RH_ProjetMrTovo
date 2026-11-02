<?php

/** Routes du squelette statique du Bloc 6 — Temps de travail et pointage. */
$routes->group('bloc6', ['namespace' => 'App\\Controllers\\Bloc6', 'filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Bloc6Controller::index');
    $routes->get('(:segment)', 'Bloc6Controller::show/$1');
});
