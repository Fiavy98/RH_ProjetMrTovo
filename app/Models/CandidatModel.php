<?php

namespace App\Models;

use App\Entities\Candidat;

/**
 * Accès à la table `candidat`.
 * Origine : Bloc 2.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class CandidatModel extends SirhModel
{
    protected $table      = 'candidat';
    protected $returnType = Candidat::class;

    protected $allowedFields = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'cv',
    ];

    protected $validationRules = [
        'nom'        => 'required|max_length[100]',
        'prenom'     => 'required|max_length[100]',
        'email'      => 'required|valid_email|max_length[150]',
        'telephone'  => 'permit_empty|max_length[30]',
        'cv'         => 'permit_empty|max_length[255]',
    ];
}
