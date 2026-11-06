<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `historique_salaire`.
 * Stocke les évolutions de rémunération d’un employé.
 */
class HistoriqueSalaire extends SirhEntity
{
    protected $casts = [
        'id'             => 'integer',
        'employe_id'     => 'integer',
        'ancien_salaire' => 'float',
        'nouveau_salaire'=> 'float',
        'date_effet'     => 'date',
        'cree_le'        => 'datetime',
    ];
}
