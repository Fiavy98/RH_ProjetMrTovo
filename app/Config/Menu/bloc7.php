<?php

return [
    'title' => 'Congés et absences',
    'items' => [
        ['label' => 'Mes congés', 'url' => 'bloc7/demandes', 'icon' => '＋', 'roles' => ['salarie']],
        ['label' => 'Demandes de congé', 'url' => 'bloc7/demandes', 'icon' => '＋', 'roles' => ['manager', 'rh', 'admin']],
        ['label' => 'Notifications', 'url' => 'bloc7/notifications', 'icon' => '◉', 'roles' => ['salarie', 'manager', 'rh', 'admin']],
        ['label' => 'Validations', 'url' => 'bloc7/validations', 'icon' => '✓', 'roles' => ['manager', 'rh', 'admin']],
        ['label' => 'Mon solde', 'url' => 'bloc7/soldes', 'icon' => '◫', 'roles' => ['salarie']],
        ['label' => 'Droits et soldes', 'url' => 'bloc7/soldes', 'icon' => '◫', 'roles' => ['manager', 'rh', 'admin']],
        ['label' => 'Absences', 'url' => 'bloc7/absences', 'icon' => '▤', 'roles' => ['rh', 'admin']],
        ['label' => 'Types de congé', 'url' => 'bloc7/types-conges', 'icon' => '⚙', 'roles' => ['rh', 'admin']],
        ['label' => 'Comptes salariés', 'url' => 'bloc7/comptes-salaries', 'icon' => '♙', 'roles' => ['rh', 'admin']],
    ],
];
