<?php

namespace App\Models;

use App\Entities\Entretien;

/**
 * Accès à la table `entretien`.
 * Origine : Bloc 2.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class EntretienModel extends SirhModel
{
    protected $table      = 'entretien';
    protected $returnType = Entretien::class;

    protected $allowedFields = [
        'candidature_id',
        'date_entretien',
        'type_entretien',
        'lieu',
        'statut',
        'compte_rendu',
    ];

    protected $validationRules = [
        'candidature_id'  => 'required|is_natural_no_zero',
        'date_entretien'  => 'required|valid_date',
        'type_entretien'  => 'permit_empty|max_length[50]',
        'lieu'            => 'permit_empty|max_length[150]',
        'statut'          => 'permit_empty|max_length[30]',
        'compte_rendu'    => 'permit_empty',
    ];
}
