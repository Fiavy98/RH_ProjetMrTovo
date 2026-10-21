<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `status`.
 * Origine : Référentiel partagé.
 *
 * Les colonnes non texte sont typées dans $casts (la base renvoie des chaînes).
 */
class Status extends SirhEntity
{
    protected $casts = [
        'id'     => 'integer',
        'actif'  => 'pgbool',
    ];
}
