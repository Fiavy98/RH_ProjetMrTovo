<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `proposition_embauche`.
 * Origine : Bloc 3.
 */
class PropositionEmbauche extends SirhEntity
{
    // Réponse du candidat
    public const REPONSE_EN_ATTENTE = 'EN_ATTENTE';
    public const REPONSE_ACCEPTEE   = 'ACCEPTEE';
    public const REPONSE_REFUSEE    = 'REFUSEE';

    // Décision finale de la direction
    public const DECISION_EN_ATTENTE = 'EN_ATTENTE';
    public const DECISION_VALIDEE    = 'VALIDEE';
    public const DECISION_REFUSEE    = 'REFUSEE';

    protected $casts = [
        'id'                      => 'integer',
        'candidature_id'          => 'integer',
        'salaire'                 => 'float',
        'type_contrat_id'         => 'integer',
        'date_debut'              => 'datetime',
        'date_proposition'        => 'datetime',
        'date_reponse'            => '?datetime',
        'date_decision_direction' => '?datetime',
    ];
}