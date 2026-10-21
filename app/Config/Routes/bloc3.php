<?php

/**
 * Routes du Bloc 3 (Sélection et embauche).
 * Fichier inclus par app/Config/Routes.php : la variable $routes est déjà disponible.
 *
 * Écrans du prototype, rendus par le contrôleur et le service du bloc.
 */
$routes->group('bloc3', ['namespace' => 'App\\Controllers\\Bloc3'], static function ($routes) {
    $routes->get('/', 'Bloc3Controller::index');
    $routes->get('(:segment)', 'Bloc3Controller::show/$1');
    $routes->post('(:segment)', 'Bloc3Controller::save/$1');
});
