<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `role`.
 * Origine : Référentiel partagé.
 *
 * Les colonnes non texte sont typées dans $casts (la base renvoie des chaînes).
 */
class Role extends SirhEntity
{
    protected $casts = [
        'id'  => 'integer',
    ];
}
