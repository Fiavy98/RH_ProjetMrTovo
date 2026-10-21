<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `type_tache_onboarding`.
 * Origine : Bloc 4 (liste fixe des 6 tâches).
 *
 * Les colonnes non texte sont typées dans $casts (la base renvoie des chaînes).
 */
class TypeTacheOnboarding extends SirhEntity
{
    protected $casts = [
        'id'     => 'integer',
        'ordre'  => 'integer',
    ];
}
