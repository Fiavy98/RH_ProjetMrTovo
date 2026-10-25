<?php

/**
 * Routes du Bloc 3 (Sélection et embauche).
 * Fichier inclus par app/Config/Routes.php : la variable $routes est déjà disponible.
 *
 * Écrans du prototype, rendus par le contrôleur et le service du bloc.
 */
$routes->group('bloc3', ['namespace' => 'App\\Controllers\\Bloc3', 'filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Bloc3Controller::index');

    $routes->get('selected', 'SelectedController::index');
    $routes->get('proposal-form', 'ProposalFormController::index');
    $routes->post('proposal-form', 'ProposalFormController::save');
    $routes->get('responses', 'ResponsesController::index');
    $routes->post('responses', 'ResponsesController::save');
    $routes->get('hire-validations', 'HireValidationsController::index');
    $routes->post('hire-validations', 'HireValidationsController::save');
    $routes->get('employees', 'EmployeesController::index');
    $routes->post('employees', 'EmployeesController::save');

    $routes->get('(:segment)', 'Bloc3Controller::show/$1');
    $routes->post('(:segment)', 'Bloc3Controller::save/$1');
});
