<?php

namespace App\Models;

use App\Entities\Onboarding;

/**
 * Accès à la table `onboarding`.
 * Origine : Bloc 4.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class OnboardingModel extends SirhModel
{
    protected $table      = 'onboarding';
    protected $returnType = Onboarding::class;

    protected $allowedFields = [
        'employe_id',
        'statut',
        'termine_le',
    ];

    protected $validationRules = [
        'employe_id'  => 'required|is_natural_no_zero',
        'statut'      => 'permit_empty|in_list[a_faire,en_cours,termine]',
        'termine_le'  => 'permit_empty|valid_date',
    ];
}
