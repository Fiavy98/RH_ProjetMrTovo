<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `evaluation_candidat`.
 * Origine : Bloc 2.
 *
 * Les colonnes non texte sont typées dans $casts (la base renvoie des chaînes).
 */
class EvaluationCandidat extends SirhEntity
{
    protected $casts = [
        'id'               => 'integer',
        'entretien_id'     => 'integer',
        'evaluateur_id'    => '?integer',
        'note'             => '?float',
        'date_evaluation'  => 'datetime',
    ];
}
