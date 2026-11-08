<?php

/** Routes du squelette statique du Bloc 6 — Temps de travail et pointage. */
$routes->group('bloc6', ['namespace' => 'App\\Controllers\\Bloc6', 'filter' => ['auth', 'internal']], static function ($routes) {
/**
 * Routes du Bloc 6 — Temps de travail et pointage.
 * Réservé exclusivement aux 3 écrans demandés :
 * - /bloc6/horaires
 * - /bloc6/planning
 * - /bloc6/pointages
 */
$routes->group('bloc6', ['namespace' => 'App\\Controllers\\Bloc6', 'filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Bloc6Controller::index');

    // 1. Horaires & Régimes
    $routes->get('horaires', 'Bloc6Controller::horaires');
    $routes->post('horaires/save', 'Bloc6Controller::saveHoraire');
    $routes->post('horaires/save/(:num)', 'Bloc6Controller::saveHoraire/$1');

    // 2. Planning
    $routes->get('planning', 'Bloc6Controller::planning');
    $routes->post('planning/save', 'Bloc6Controller::savePlanning');

    // 3. Pointages
    $routes->get('pointages', 'Bloc6Controller::pointages');
    $routes->post('pointages/save', 'Bloc6Controller::savePointage');
});

