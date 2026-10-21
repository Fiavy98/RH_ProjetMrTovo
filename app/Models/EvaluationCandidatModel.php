<?php

namespace App\Models;

use App\Entities\EvaluationCandidat;

/**
 * Accès à la table `evaluation_candidat`.
 * Origine : Bloc 2.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class EvaluationCandidatModel extends SirhModel
{
    protected $table      = 'evaluation_candidat';
    protected $returnType = EvaluationCandidat::class;

    protected $allowedFields = [
        'entretien_id',
        'evaluateur_id',
        'note',
        'commentaire',
        'decision',
    ];

    protected $validationRules = [
        'entretien_id'   => 'required|is_natural_no_zero',
        'evaluateur_id'  => 'permit_empty|is_natural_no_zero',
        'note'           => 'permit_empty|numeric',
        'commentaire'    => 'permit_empty',
        'decision'       => 'permit_empty|max_length[30]',
    ];
}
