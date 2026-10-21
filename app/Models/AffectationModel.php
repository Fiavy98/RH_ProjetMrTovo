<?php

namespace App\Models;

use App\Entities\Affectation;

/**
 * Accès à la table `affectation`.
 * Origine : Bloc 3 (gérée par le Bloc 3, suivie par le Bloc 4).
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class AffectationModel extends SirhModel
{
    protected $table      = 'affectation';
    protected $returnType = Affectation::class;

    protected $allowedFields = [
        'employe_id',
        'poste_id',
        'date_debut',
        'date_fin',
        'cree_par',
    ];

    protected $validationRules = [
        'employe_id'  => 'required|is_natural_no_zero',
        'poste_id'    => 'required|is_natural_no_zero',
        'date_debut'  => 'required|valid_date[Y-m-d]',
        'date_fin'    => 'permit_empty|valid_date[Y-m-d]',
        'cree_par'    => 'permit_empty|is_natural_no_zero',
    ];
}
