<?php

/**
 * Routes du Bloc 2 (Recrutement).
 * Fichier inclus par app/Config/Routes.php : la variable $routes est déjà disponible.
 *
 * Écrans du prototype, rendus par le contrôleur et le service du bloc.
 */
$routes->group('bloc2', ['namespace' => 'App\\Controllers\\Bloc2'], static function ($routes) {
    $routes->get('/', 'Bloc2Controller::index');
    $routes->get('dashboard', 'Bloc2Controller::dashboard');
    $routes->get('offers', 'Bloc2Controller::offers');
    $routes->get('offers/new', 'Bloc2Controller::offerForm');
    $routes->get('offers/(:num)', 'Bloc2Controller::offerForm/$1');
    $routes->post('offers/save', 'Bloc2Controller::saveOffer');
    $routes->post('offers/(:num)/publish', 'Bloc2Controller::publishOffer/$1');
    $routes->post('offers/(:num)/close', 'Bloc2Controller::closeOffer/$1');
    $routes->get('candidates', 'Bloc2Controller::candidates');
    $routes->get('candidates/new', 'Bloc2Controller::candidateForm');
    $routes->get('candidates/(:num)', 'Bloc2Controller::candidateDetail/$1');
    $routes->get('candidates/(:num)/edit', 'Bloc2Controller::candidateForm/$1');
    $routes->post('candidates/save', 'Bloc2Controller::saveCandidate');
    $routes->get('applications', 'Bloc2Controller::applications');
    $routes->get('applications/new', 'Bloc2Controller::applicationForm');
    $routes->post('applications/save', 'Bloc2Controller::saveApplication');
    $routes->post('applications/(:num)/status', 'Bloc2Controller::applicationStatus/$1');
    $routes->get('preselection', 'Bloc2Controller::preselection');
    $routes->get('interviews', 'Bloc2Controller::interviews');
    $routes->get('interviews/new', 'Bloc2Controller::interviewForm');
    $routes->get('interviews/(:num)/edit', 'Bloc2Controller::interviewForm/$1');
    $routes->post('interviews/save', 'Bloc2Controller::saveInterview');
    $routes->post('interviews/(:num)/status', 'Bloc2Controller::interviewStatus/$1');
    $routes->get('evaluations', 'Bloc2Controller::evaluations');
    $routes->get('evaluations/(:num)', 'Bloc2Controller::evaluations/$1');
    $routes->post('evaluations/save', 'Bloc2Controller::saveEvaluation');
    $routes->get('decisions', 'Bloc2Controller::decisions');
    $routes->post('applications/(:num)/decision', 'Bloc2Controller::finalDecision/$1');
    $routes->get('recruitment-history', 'Bloc2Controller::history');
});
