<?php

/**
 * Entrées de menu du Bloc 4, accessibles à tous les utilisateurs connectés.
 */
return [
    'title' => 'Contrats',
    'items' => [
        ['label' => 'Liste des contrats', 'url' => 'bloc4/contracts', 'icon' => '▤', 'roles' => ['rh', 'admin']],
        ['label' => 'Nouveau contrat', 'url' => 'bloc4/contract-form', 'icon' => '＋', 'roles' => ['rh', 'admin']],
        ['label' => 'Renouvellements', 'url' => 'bloc4/renewals', 'icon' => '⟳', 'roles' => ['rh', 'admin']],
        ['label' => 'Affectations', 'url' => 'bloc4/assignment-b4', 'icon' => '▥', 'roles' => ['rh', 'admin']],
        ['label' => 'Documents', 'url' => 'bloc4/documents', 'icon' => '▢', 'roles' => ['rh', 'admin']],
        ['label' => 'Onboardings', 'url' => 'bloc4/onboardings', 'icon' => '◴', 'roles' => ['rh', 'manager', 'admin']],
        ['label' => 'Paramètres contrats', 'url' => 'bloc4/contract-settings', 'icon' => '⚙', 'roles' => ['rh', 'admin']],
    ],
];
