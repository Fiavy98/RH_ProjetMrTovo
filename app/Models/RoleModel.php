<?php

namespace App\Models;

use App\Entities\Role;

/**
 * Accès à la table `role`.
 * Origine : Référentiel partagé.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class RoleModel extends SirhModel
{
    protected $table      = 'role';
    protected $returnType = Role::class;

    protected $allowedFields = [
        'nom_role',
        'description',
    ];

    protected $validationRules = [
        'nom_role'     => 'required|max_length[50]',
        'description'  => 'permit_empty|max_length[255]',
    ];
}
