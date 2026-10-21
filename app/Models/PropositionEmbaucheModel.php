<?php

namespace App\Models;

use App\Entities\PropositionEmbauche;

/**
 * Accès à la table `proposition_embauche`.
 * Origine : Bloc 3.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class PropositionEmbaucheModel extends SirhModel
{
    protected $table      = 'proposition_embauche';
    protected $returnType = PropositionEmbauche::class;

    protected $allowedFields = [
        'candidature_id',
        'salaire',
        'type_contrat_id',
        'date_debut',
        'reponse',
        'date_reponse',
    ];

    protected $validationRules = [
        'candidature_id'   => 'required|is_natural_no_zero',
        'salaire'          => 'required|numeric|greater_than_equal_to[0]',
        'type_contrat_id'  => 'required|is_natural_no_zero',
        'date_debut'       => 'required|valid_date[Y-m-d]',
        'reponse'          => 'permit_empty|in_list[EN_ATTENTE,ACCEPTEE,REFUSEE]',
        'date_reponse'     => 'permit_empty|valid_date',
    ];
}
