<?php

namespace App\Models;

use App\Entities\Status;

/**
 * Accès à la table `status`.
 * Origine : Référentiel partagé.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class StatusModel extends SirhModel
{
    protected $table      = 'status';
    protected $returnType = Status::class;

    protected $allowedFields = [
        'code',
        'libelle',
        'description',
        'actif',
    ];

    protected $validationRules = [
        'code'         => 'required|max_length[50]',
        'libelle'      => 'required|max_length[100]',
        'description'  => 'permit_empty|max_length[255]',
        'actif'        => 'permit_empty',
    ];
}
