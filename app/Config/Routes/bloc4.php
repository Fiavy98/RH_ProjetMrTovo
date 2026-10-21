<?php

/**
 * Routes du Bloc 4 (Contrat et onboarding).
 * Fichier inclus par app/Config/Routes.php : la variable $routes est déjà disponible.
 *
 * Écrans du prototype, rendus par le contrôleur et le service du bloc.
 */
$routes->group('bloc4', ['namespace' => 'App\\Controllers\\Bloc4'], static function ($routes) {
    $routes->get('/', 'Bloc4Controller::index');
    $routes->get('(:segment)', 'Bloc4Controller::show/$1');
    $routes->post('(:segment)', 'Bloc4Controller::save/$1');
});
