<?php

namespace App\Models;

use App\Entities\Demande;

/**
 * Accès à la table `demande`.
 * Origine : Bloc 1.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class DemandeModel extends SirhModel
{
    protected $table      = 'demande';
    protected $returnType = Demande::class;

    protected $allowedFields = [
        'intitule',
        'nombre_postes',
        'motif',
        'budget_min',
        'budget_max',
        'type_contrat_id',
        'status_id',
        'demandeur_id',
        'departement_id'
    ];

    protected $validationRules = [
        'intitule'        => 'required|max_length[150]',
        'nombre_postes'   => 'required|is_natural_no_zero',
        'motif'           => 'required',
        'budget_min'      => 'permit_empty|numeric|greater_than_equal_to[0]',
        'budget_max'      => 'permit_empty|numeric|greater_than_equal_to[0]',
        'type_contrat_id' => 'required|is_natural_no_zero',
        'status_id'       => 'required|is_natural_no_zero',
        'demandeur_id'    => 'required|is_natural_no_zero',
        'departement_id'  => 'required|is_natural_no_zero',
    ];
}
