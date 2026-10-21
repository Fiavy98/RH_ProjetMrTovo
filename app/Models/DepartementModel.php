<?php

namespace App\Models;

use App\Entities\Departement;

/**
 * Accès à la table `departement`.
 * Origine : Référentiel partagé.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class DepartementModel extends SirhModel
{
    protected $table      = 'departement';
    protected $returnType = Departement::class;

    protected $allowedFields = [
        'nom_departement',
        'description',
    ];

    protected $validationRules = [
        'nom_departement'  => 'required|max_length[100]',
        'description'      => 'permit_empty',
    ];
}
