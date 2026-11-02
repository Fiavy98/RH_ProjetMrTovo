<?php

/** Routes du squelette statique du Bloc 5 — Dossier administratif. */
$routes->group('bloc5', ['namespace' => 'App\\Controllers\\Bloc5', 'filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Bloc5Controller::index');
    $routes->get('(:segment)', 'Bloc5Controller::show/$1');
});
