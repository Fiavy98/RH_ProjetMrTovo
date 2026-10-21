<?php

namespace App\Models;

use App\Entities\TypeTacheOnboarding;

/**
 * Accès à la table `type_tache_onboarding`.
 * Origine : Bloc 4 (liste fixe des 6 tâches).
 *
 * Règles de validation : elles reprennent les contraintes du script SQL (NOT NULL, longueurs,
 * CHECK). L'unicité (UNIQUE) et les contraintes entre colonnes (dates, chevauchements) restent
 * garanties par la base ; les règles métier iront dans les Services du bloc.
 * Une mise à jour valide toutes les règles : envoyer tous les champs du formulaire.
 */
class TypeTacheOnboardingModel extends SirhModel
{
    protected $table      = 'type_tache_onboarding';
    protected $returnType = TypeTacheOnboarding::class;

    protected $allowedFields = [
        'code',
        'libelle',
        'ordre',
    ];

    protected $validationRules = [
        'code'     => 'required|max_length[30]',
        'libelle'  => 'required|max_length[100]',
        'ordre'    => 'required|is_natural_no_zero',
    ];
}
