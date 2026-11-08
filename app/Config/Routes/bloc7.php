<?php

/** Routes métier du Bloc 7, partie C et consultations de la partie D. */
$routes->group('bloc7', ['namespace'=>'App\\Controllers\\Bloc7','filter'=>['auth','bloc7access']], static function($routes) {
    $routes->get('/', 'Bloc7Controller::index');
    $routes->get('demandes', 'Bloc7Controller::demandes');
    $routes->get('demandes/nouvelle', 'Bloc7Controller::formulaire');
    $routes->post('demandes/nouvelle', 'Bloc7Controller::enregistrer');
    $routes->get('demandes/(:num)/modifier', 'Bloc7Controller::formulaire/$1');
    $routes->post('demandes/(:num)/modifier', 'Bloc7Controller::enregistrer/$1');
    $routes->post('demandes/(:num)/soumettre', 'Bloc7Controller::soumettre/$1');
    $routes->get('demandes/(:num)', 'Bloc7Controller::detail/$1');
    $routes->post('demandes/(:num)/retirer', 'Bloc7Controller::retirer/$1');
    $routes->get('validations', 'Bloc7Controller::validations');
    $routes->post('validations/(:num)/decision', 'Bloc7Controller::decider/$1');
    $routes->get('soldes', 'Bloc7Controller::soldes');
    $routes->post('soldes/droits', 'Bloc7Controller::attribuerDroit');
    $routes->get('types-conges', 'Bloc7Controller::types');
    $routes->post('types-conges', 'Bloc7Controller::enregistrerType');
    $routes->post('jours-feries', 'Bloc7Controller::enregistrerJourFerie');
    $routes->get('comptes-salaries', 'Bloc7Controller::comptesSalaries');
    $routes->post('comptes-salaries', 'Bloc7Controller::enregistrerCompteSalarie');
    $routes->get('absences', 'Bloc7Controller::absences');
    $routes->get('notifications', 'Bloc7Controller::notifications');
    $routes->post('notifications/lues', 'Bloc7Controller::marquerNotificationsLues');
    $routes->get('justificatifs/(:num)/download', 'Bloc7Controller::telechargerJustificatif/$1');
});
