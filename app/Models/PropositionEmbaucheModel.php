<?php

namespace App\Models;

use App\Entities\PropositionEmbauche;

/**
 * Accès à la table `proposition_embauche`.
 * Origine : Bloc 3.
 */
class PropositionEmbaucheModel extends SirhModel
{
    protected $table       = 'proposition_embauche';
    protected $returnType  = PropositionEmbauche::class;

    protected $allowedFields = [
        'candidature_id',
        'salaire',
        'type_contrat_id',
        'date_debut',
        'date_proposition',

        // Réponse du candidat
        'reponse',
        'date_reponse',

        // Décision de la direction
        'decision_direction',
        'date_decision_direction',
        'commentaire_direction',
    ];

    protected $validationRules = [
        'candidature_id' => 'required|is_natural_no_zero',
        'salaire'        => 'required|numeric|greater_than_equal_to[0]',
        'type_contrat_id' => 'required|is_natural_no_zero',
        'date_debut'      => 'required|valid_date[Y-m-d]',

        // Réponse du candidat
        'reponse' => 'permit_empty|in_list[EN_ATTENTE,ACCEPTEE,REFUSEE]',
        'date_reponse' => 'permit_empty|valid_date',

        // Décision de la direction
        'decision_direction' => 'permit_empty|in_list[EN_ATTENTE,VALIDEE,REFUSEE]',
        'date_decision_direction' => 'permit_empty|valid_date',

        // Commentaire de la direction
        'commentaire_direction' => 'permit_empty|string',
    ];
}