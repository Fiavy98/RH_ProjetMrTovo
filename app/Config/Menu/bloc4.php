<?php

/**
 * Entrées de menu du Bloc 4, accessibles à tous les utilisateurs connectés.
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
        ['label' => 'Paramètres contrats', 'url' => 'bloc4/contract-settings', 'icon' => '⚙'],
    ],
];
