<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `candidat`.
 * Origine : Bloc 2.
 *
 * Les colonnes non texte sont typées dans $casts (la base renvoie des chaînes).
 */
class Candidat extends SirhEntity
{
    protected $casts = [
        'id'             => 'integer',
        'date_creation'  => 'datetime',
    ];
}
