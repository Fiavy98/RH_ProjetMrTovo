<?php

/** Menu du recrutement, visible uniquement par le rôle RH de démonstration. */
return [
    'title' => 'Bloc 2 · Recrutement',
    'items' => [
        ['label' => 'Suivi du recrutement', 'url' => 'bloc2/dashboard', 'icon' => '◧', 'roles' => ['rh']],
        ['label' => 'Offres d’emploi', 'url' => 'bloc2/offers', 'icon' => '▤', 'roles' => ['rh']],
        ['label' => 'Créer une offre', 'url' => 'bloc2/offers/new', 'icon' => '＋', 'roles' => ['rh']],
        ['label' => 'Candidats', 'url' => 'bloc2/candidates', 'icon' => '♙', 'roles' => ['rh']],
        ['label' => 'Candidatures', 'url' => 'bloc2/applications', 'icon' => '◇', 'roles' => ['rh']],
        ['label' => 'Présélection', 'url' => 'bloc2/preselection', 'icon' => '✓', 'roles' => ['rh']],
        ['label' => 'Entretiens', 'url' => 'bloc2/interviews', 'icon' => '▷', 'roles' => ['rh']],
        ['label' => 'Évaluations', 'url' => 'bloc2/evaluations', 'icon' => '◉', 'roles' => ['rh']],
        ['label' => 'Décision finale', 'url' => 'bloc2/decisions', 'icon' => '⚑', 'roles' => ['rh']],
        ['label' => 'Historique recrutement', 'url' => 'bloc2/recruitment-history', 'icon' => '◴', 'roles' => ['rh']],
    ],
];
