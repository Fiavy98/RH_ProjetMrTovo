<?php

/**
 * Routes du Bloc 2 (Recrutement).
 * Fichier inclus par app/Config/Routes.php : la variable $routes est déjà disponible.
 *
 * Écrans du prototype, rendus par le contrôleur et le service du bloc.
 */
$routes->group('bloc2', ['namespace' => 'App\\Controllers\\Bloc2'], static function ($routes) {
    $routes->get('/', 'Bloc2Controller::index');
    $routes->get('(:segment)', 'Bloc2Controller::show/$1');
    $routes->post('(:segment)', 'Bloc2Controller::save/$1');
});
