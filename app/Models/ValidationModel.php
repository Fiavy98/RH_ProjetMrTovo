<?php

namespace App\Models;

use App\Entities\Validation;

/**
 * Accès à la table `validation`.
 * Origine : Bloc 1.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class ValidationModel extends SirhModel
{
    protected $table      = 'validation';
    protected $returnType = Validation::class;

    protected $allowedFields = [
        'demande_id',
        'validateur_id',
        'status_id',
        'commentaire',
        'date_validation',
    ];

    protected $validationRules = [
        'demande_id'       => 'required|is_natural_no_zero',
        'validateur_id'    => 'required|is_natural_no_zero',
        'status_id'        => 'required|is_natural_no_zero',
        'commentaire'      => 'permit_empty',
        'date_validation'  => 'permit_empty|valid_date',
    ];
}
