<?php

namespace App\Models;

use App\Entities\Poste;

/**
 * Accès à la table `poste`.
 * Origine : Bloc 1.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class PosteModel extends SirhModel
{
    protected $table      = 'poste';
    protected $returnType = Poste::class;

    protected $allowedFields = [
        'intitule',
        'description',
        'competences_requises',
        'demande_id',
        'departement_id',
        'status_id',
    ];

    protected $validationRules = [
        'intitule'              => 'required|max_length[150]',
        'description'           => 'permit_empty',
        'competences_requises'  => 'permit_empty',
        'demande_id'            => 'required|is_natural_no_zero',
        'departement_id'        => 'required|is_natural_no_zero',
        'status_id'             => 'required|is_natural_no_zero',
    ];
}
