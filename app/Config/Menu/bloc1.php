<?php

/**
 * Entrées de menu du Bloc 1. Les droits détaillés sont vérifiés par le filtre du bloc.
 */
return [
    'title' => 'Bloc 1 · Besoins',
    'items' => [
        ['label' => 'Vue des demandes', 'url' => 'bloc1/dashboard', 'icon' => '◧'],
        ['label' => 'Mes demandes', 'url' => 'bloc1/requests', 'icon' => '▤'],
        ['label' => 'Nouvelle demande', 'url' => 'bloc1/demande-form', 'icon' => '＋'],
        ['label' => 'Analyse RH', 'url' => 'bloc1/rh-review', 'icon' => '✧', 'roles' => ['rh', 'direction', 'admin']],
        ['label' => 'Avis Finance', 'url' => 'bloc1/finance-review', 'icon' => '◈', 'roles' => ['finance', 'direction', 'admin']],
        ['label' => 'Décisions Direction', 'url' => 'bloc1/direction-review', 'icon' => '◆', 'roles' => ['direction', 'admin']],
        ['label' => 'Postes associés', 'url' => 'bloc1/positions', 'icon' => '▥'],
        ['label' => 'Historique demandes', 'url' => 'bloc1/history', 'icon' => '◷', 'roles' => ['rh', 'finance', 'direction', 'admin']],
    ],
];
