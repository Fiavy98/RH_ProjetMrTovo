<?php

/** Menu du recrutement, accessible à tous les utilisateurs connectés. */
return [
    'title' => 'Bloc 2 · Recrutement',
    'items' => [
        ['label' => 'Suivi du recrutement', 'url' => 'bloc2/dashboard', 'icon' => '◧'],
        ['label' => 'Offres d’emploi', 'url' => 'bloc2/offers', 'icon' => '▤'],
        ['label' => 'Créer une offre', 'url' => 'bloc2/offers/new', 'icon' => '＋'],
        ['label' => 'Candidats', 'url' => 'bloc2/candidates', 'icon' => '♙'],
        ['label' => 'Candidatures', 'url' => 'bloc2/applications', 'icon' => '◇'],
        ['label' => 'Présélection', 'url' => 'bloc2/preselection', 'icon' => '✓'],
        ['label' => 'Entretiens', 'url' => 'bloc2/interviews', 'icon' => '▷'],
        ['label' => 'Évaluations', 'url' => 'bloc2/evaluations', 'icon' => '◉'],
        ['label' => 'Décision finale', 'url' => 'bloc2/decisions', 'icon' => '⚑'],
        ['label' => 'Historique recrutement', 'url' => 'bloc2/recruitment-history', 'icon' => '◴'],
    ],
];
