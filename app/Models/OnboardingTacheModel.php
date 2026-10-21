<?php

namespace App\Models;

use App\Entities\OnboardingTache;

/**
 * Accès à la table `onboarding_tache`.
 * Origine : Bloc 4.
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class OnboardingTacheModel extends SirhModel
{
    protected $table      = 'onboarding_tache';
    protected $returnType = OnboardingTache::class;

    protected $allowedFields = [
        'onboarding_id',
        'type_tache_id',
        'responsable_id',
        'echeance',
        'statut',
        'date_realisation',
    ];

    protected $validationRules = [
        'onboarding_id'     => 'required|is_natural_no_zero',
        'type_tache_id'     => 'required|is_natural_no_zero',
        'responsable_id'    => 'permit_empty|is_natural_no_zero',
        'echeance'          => 'permit_empty|valid_date[Y-m-d]',
        'statut'            => 'permit_empty|in_list[a_faire,en_cours,termine]',
        'date_realisation'  => 'permit_empty|valid_date[Y-m-d]',
    ];
}
