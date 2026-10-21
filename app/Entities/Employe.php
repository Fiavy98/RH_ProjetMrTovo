<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `employe`.
 * Origine : Bloc 1 (table partagée par tous les blocs).
 *
 * Les colonnes non texte sont typées dans $casts (la base renvoie des chaînes).
 */
class Employe extends SirhEntity
{
    protected $casts = [
        'id'              => 'integer',
        'departement_id'  => 'integer',
        'role_id'         => '?integer',
        'candidat_id'     => 'integer',
        'date_embauche'   => 'datetime',
        'actif'           => 'pgbool',
    ];
}
