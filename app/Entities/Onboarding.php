<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `onboarding`.
 * Origine : Bloc 4.
 *
 * Les constantes ci-dessous reprennent les valeurs de la base ; si le vocabulaire des
 * statuts change (majuscules/minuscules, nouveaux statuts), on ne modifie qu'ici.
 */
class Onboarding extends SirhEntity
{
    public const STATUT_A_FAIRE = 'a_faire';
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_TERMINE = 'termine';

    protected $casts = [
        'id'          => 'integer',
        'employe_id'  => 'integer',
        'cree_le'     => 'datetime',
        'termine_le'  => '?datetime',
    ];
}
