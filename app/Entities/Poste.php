<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `poste`.
 * Origine : Bloc 1.
 *
 * Les colonnes non texte sont typées dans $casts (la base renvoie des chaînes).
 */
class Poste extends SirhEntity
{
    protected $casts = [
        'id'              => 'integer',
        'demande_id'      => 'integer',
        'departement_id'  => 'integer',
        'status_id'       => 'integer',
    ];
}
