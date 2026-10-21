<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `departement`.
 * Origine : Référentiel partagé.
 *
 * Les colonnes non texte sont typées dans $casts (la base renvoie des chaînes).
 */
class Departement extends SirhEntity
{
    protected $casts = [
        'id'  => 'integer',
    ];
}
