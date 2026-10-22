<?php

/**
 * Entrées de menu du Bloc 1. Une entrée n'apparaît que si le rôle courant (sélecteur de démonstration)
 * figure dans 'roles' ; sans 'roles', elle est visible par tous.
 * Rôles disponibles : manager, rh, direction, admin.
 */
return [
    'title' => 'Bloc 1 · Besoins',
    'items' => [
        ['label' => 'Vue des demandes', 'url' => 'bloc1/dashboard', 'icon' => '◧'],
        ['label' => 'Mes demandes', 'url' => 'bloc1/requests', 'icon' => '▤'],
        ['label' => 'Nouvelle demande', 'url' => 'bloc1/demande-form', 'icon' => '＋'],
        ['label' => 'Analyse RH', 'url' => 'bloc1/rh-review', 'icon' => '✧'],
        ['label' => 'Avis Finance', 'url' => 'bloc1/finance-review', 'icon' => '◈'],
        ['label' => 'Décisions Direction', 'url' => 'bloc1/direction-review', 'icon' => '◆'],
        ['label' => 'Postes associés', 'url' => 'bloc1/positions', 'icon' => '▥'],
        ['label' => 'Historique demandes', 'url' => 'bloc1/history', 'icon' => '◷'],
    ],
];
