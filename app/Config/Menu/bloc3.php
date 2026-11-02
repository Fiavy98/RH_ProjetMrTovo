<?php

/**
 * Entrées de menu du Bloc 3, accessibles à tous les utilisateurs connectés.
 */
return [
    'title' => 'Embauche',
    'items' => [
        ['label' => 'Candidats retenus', 'url' => 'bloc3/selected', 'icon' => '♟'],
        ['label' => 'Proposition d’embauche', 'url' => 'bloc3/proposal-form', 'icon' => '＋'],
        ['label' => 'Réponses candidats', 'url' => 'bloc3/responses', 'icon' => '✉'],
        ['label' => 'Validation Direction', 'url' => 'bloc3/hire-validations', 'icon' => '◆'],
        ['label' => 'Dossiers salariés', 'url' => 'bloc3/employees', 'icon' => '◪'],
    ],
];
