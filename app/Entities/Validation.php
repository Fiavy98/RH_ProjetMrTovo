<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `validation`.
 * Origine : Bloc 1.
 *
 * Les colonnes non texte sont typées dans $casts (la base renvoie des chaînes).
 */
class Validation extends SirhEntity
{
    protected $casts = [
        'id'               => 'integer',
        'demande_id'       => 'integer',
        'validateur_id'    => 'integer',
        'status_id'        => 'integer',
        'date_validation'  => '?datetime',
    ];
}
