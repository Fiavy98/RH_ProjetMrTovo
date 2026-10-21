<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `affectation`.
 * Origine : Bloc 3 (gérée par le Bloc 3, suivie par le Bloc 4).
 *
 * Les colonnes non texte sont typées dans $casts (la base renvoie des chaînes).
 */
class Affectation extends SirhEntity
{
    protected $casts = [
        'id'          => 'integer',
        'employe_id'  => 'integer',
        'poste_id'    => 'integer',
        'date_debut'  => 'datetime',
        'date_fin'    => '?datetime',
        'cree_le'     => 'datetime',
        'cree_par'    => '?integer',
    ];
}
