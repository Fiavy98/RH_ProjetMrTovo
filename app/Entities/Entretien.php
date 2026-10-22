<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `entretien`.
 * Origine : Bloc 2.
 *
 * Les constantes ci-dessous reprennent les valeurs de la base ; si le vocabulaire des
 * statuts change (majuscules/minuscules, nouveaux statuts), on ne modifie qu'ici.
 */
class Entretien extends SirhEntity
{
    public const STATUT_PLANIFIE = 'PLANIFIE';
    public const STATUT_REALISE = 'REALISE';
    public const STATUT_ANNULE = 'ANNULE';

    protected $casts = [
        'id'              => 'integer',
        'candidature_id'  => 'integer',
        'date_entretien'  => 'datetime',
    ];
}
