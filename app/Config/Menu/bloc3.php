<?php

/**
 * Entrées de menu du Bloc 3, accessibles à tous les utilisateurs connectés.
 */
return [
    'title' => 'Embauche',
    'items' => [
        ['label' => 'Candidats retenus', 'url' => 'bloc3/selected', 'icon' => '♟', 'roles' => ['rh', 'admin']],
        ['label' => 'Proposition d’embauche', 'url' => 'bloc3/proposal-form', 'icon' => '＋', 'roles' => ['rh', 'admin']],
        ['label' => 'Réponses candidats', 'url' => 'bloc3/responses', 'icon' => '✉', 'roles' => ['rh', 'admin']],
        ['label' => 'Validation Direction', 'url' => 'bloc3/hire-validations', 'icon' => '◆', 'roles' => ['direction', 'admin']],
        ['label' => 'Dossiers salariés', 'url' => 'bloc3/employees', 'icon' => '◪', 'roles' => ['rh', 'admin']],
    ],
];
