<?php

/**
 * Entrées de menu du Bloc 3, accessibles à tous les utilisateurs connectés.
 */
return [
    'title' => 'Bloc 3 · Embauche',
    'items' => [
        ['label' => 'Suivi des embauches', 'url' => 'bloc3/dashboard', 'icon' => '◧'],
        ['label' => 'Candidats retenus', 'url' => 'bloc3/selected', 'icon' => '♟'],
        ['label' => 'Proposition d’embauche', 'url' => 'bloc3/proposal-form', 'icon' => '＋'],
        ['label' => 'Réponses candidats', 'url' => 'bloc3/responses', 'icon' => '✉'],
        ['label' => 'Validation Direction', 'url' => 'bloc3/hire-validations', 'icon' => '◆'],
        ['label' => 'Dossiers salariés', 'url' => 'bloc3/employees', 'icon' => '◪'],
        ['label' => 'Affectations initiales', 'url' => 'bloc3/assignment-b3', 'icon' => '▥'],
        ['label' => 'Transmission au Bloc 4', 'url' => 'bloc3/handoff', 'icon' => '⇢'],
    ],
];
