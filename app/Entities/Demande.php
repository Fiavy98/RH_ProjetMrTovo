<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `demande`.
 * Origine : Bloc 1.
 *
 * Les colonnes non texte sont typées dans $casts (la base renvoie des chaînes).
 */
class Demande extends SirhEntity
{
    protected $casts = [
        'id'              => 'integer',
        'nombre_postes'   => 'integer',
        'date_demande'    => 'datetime',
        'budget_min'      => '?float',
        'budget_max'      => '?float',
        'type_contrat_id' => 'integer',
        'status_id'       => 'integer',
        'demandeur_id'    => 'integer',
        'departement_id'  => 'integer',
    ];
}
