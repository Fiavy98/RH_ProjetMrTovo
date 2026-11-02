<?php

/** Menu du recrutement, filtré selon le rôle courant. */
return [
    'title' => 'Recrutement',
    'items' => [
        ['label' => 'Suivi du recrutement', 'url' => 'bloc2/dashboard', 'icon' => '◧', 'roles' => ['rh', 'admin']],
        ['label' => 'Offres d’emploi', 'url' => 'bloc2/offers', 'icon' => '▤', 'roles' => ['rh', 'admin']],
        ['label' => 'Créer une offre', 'url' => 'bloc2/offers/new', 'icon' => '＋', 'roles' => ['rh', 'admin']],
        ['label' => 'Candidats', 'url' => 'bloc2/candidates', 'icon' => '♙', 'roles' => ['rh', 'admin']],
        ['label' => 'Candidatures', 'url' => 'bloc2/applications', 'icon' => '◇', 'roles' => ['rh', 'admin']],
        ['label' => 'Présélection', 'url' => 'bloc2/preselection', 'icon' => '✓', 'roles' => ['rh', 'admin']],
        ['label' => 'Entretiens', 'url' => 'bloc2/interviews', 'icon' => '▷', 'roles' => ['rh', 'manager', 'admin']],
        ['label' => 'Évaluations', 'url' => 'bloc2/evaluations', 'icon' => '◉', 'roles' => ['rh', 'manager', 'admin']],
        ['label' => 'Décision finale', 'url' => 'bloc2/decisions', 'icon' => '⚑', 'roles' => ['rh', 'admin']],
        ['label' => 'Historique recrutement', 'url' => 'bloc2/recruitment-history', 'icon' => '◴', 'roles' => ['rh', 'admin']],
    ],
];
