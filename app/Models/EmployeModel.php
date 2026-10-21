<?php

namespace App\Models;

use App\Entities\Employe;

/**
 * Accès à la table `employe`.
 * Origine : Bloc 1 (table partagée par tous les blocs).
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class EmployeModel extends SirhModel
{
    protected $table      = 'employe';
    protected $returnType = Employe::class;

    // `mot_de_passe` est volontairement absent : l'authentification est repoussée (P4), on ne stocke aucun mot de passe.
    protected $allowedFields = [
        'matricule',
        'nom',
        'prenom',
        'email',
        'departement_id',
        'role_id',
        'candidat_id',
        'date_embauche',
        'actif',
    ];

    protected $validationRules = [
        'matricule'       => 'required|max_length[20]',
        'nom'             => 'required|max_length[100]',
        'prenom'          => 'required|max_length[100]',
        'email'           => 'required|valid_email|max_length[255]',
        'departement_id'  => 'required|is_natural_no_zero',
        'role_id'         => 'permit_empty|is_natural_no_zero',
        'candidat_id'     => 'required|is_natural_no_zero',
        'date_embauche'   => 'required|valid_date[Y-m-d]',
        'actif'           => 'permit_empty',
    ];
}
