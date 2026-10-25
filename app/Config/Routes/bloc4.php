<?php

/**
 * Routes du Bloc 4 (Contrat et onboarding).
 * Fichier inclus par app/Config/Routes.php : la variable $routes est déjà disponible.
 *
 * Écrans du prototype, rendus par le contrôleur et le service du bloc.
 */
$routes->group('bloc4', ['namespace' => 'App\\Controllers\\Bloc4', 'filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Bloc4Controller::index');
    $routes->get('contracts', 'ContratController::index');
    $routes->get('contract-form', 'ContratController::createForm');
    $routes->post('contract-form', 'ContratController::save');
    $routes->get('contract-form/(:num)', 'ContratController::editForm/$1');
    $routes->post('contract-form/(:num)', 'ContratController::save/$1');
    $routes->get('contract-detail/(:num)', 'ContratController::detail/$1');
    $routes->post('contract-detail/(:num)/(:segment)', 'ContratController::transition/$1/$2');
    $routes->get('renewals', 'ContratController::renewals');
    $routes->get('renewals/(:num)', 'ContratController::renewalForm/$1');
    $routes->post('renewals/(:num)', 'ContratController::renew/$1');
    $routes->get('contract-settings', 'ContratController::settings');
    $routes->get('assignment-b4', 'AffectationController::index');
    $routes->post('assignment-b4', 'AffectationController::save');
    $routes->get('documents', 'DocumentController::index');
    $routes->get('document-form', 'DocumentController::create');
    $routes->post('document-form', 'DocumentController::save');
    $routes->get('documents/(:num)/download', 'DocumentController::download/$1');
    $routes->get('onboardings', 'OnboardingController::index');
    $routes->get('onboarding-detail/(:num)', 'OnboardingController::show/$1');
    $routes->post('onboarding-detail/(:num)', 'OnboardingController::save/$1');
    $routes->get('(:segment)', 'Bloc4Controller::show/$1');
    $routes->post('(:segment)', 'Bloc4Controller::save/$1');
});
