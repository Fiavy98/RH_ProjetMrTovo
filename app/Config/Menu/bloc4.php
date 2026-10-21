<?php

/**
 * Entrées de menu du Bloc 4. Une entrée n'apparaît que si le rôle courant (sélecteur de démonstration)
 * figure dans 'roles' ; sans 'roles', elle est visible par tous.
 * Rôles disponibles : manager, rh, direction, admin.
 */
return [
    'title' => 'Bloc 4 · Contrats',
    'items' => [
        ['label' => 'Suivi contrats et onboarding', 'url' => 'bloc4/dashboard', 'icon' => '◧'],
        ['label' => 'Liste des contrats', 'url' => 'bloc4/contracts', 'icon' => '▤'],
        ['label' => 'Nouveau contrat', 'url' => 'bloc4/contract-form', 'icon' => '＋'],
        ['label' => 'Renouvellements', 'url' => 'bloc4/renewals', 'icon' => '⟳'],
        ['label' => 'Affectations', 'url' => 'bloc4/assignment-b4', 'icon' => '▥'],
        ['label' => 'Documents', 'url' => 'bloc4/documents', 'icon' => '▢'],
        ['label' => 'Onboardings', 'url' => 'bloc4/onboardings', 'icon' => '◴'],
        ['label' => 'Suivi des 6 tâches', 'url' => 'bloc4/onboarding-detail', 'icon' => '✓'],
        ['label' => 'Paramètres contrats', 'url' => 'bloc4/contract-settings', 'icon' => '⚙'],
    ],
];
