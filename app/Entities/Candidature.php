<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `candidature`.
 * Origine : Bloc 2.
 *
 * Les constantes ci-dessous reprennent les valeurs de la base ; si le vocabulaire des
 * statuts change (majuscules/minuscules, nouveaux statuts), on ne modifie qu'ici.
 */
class Candidature extends SirhEntity
{
    public const STATUT_EN_ATTENTE = 'EN_ATTENTE';
    public const STATUT_PRESELECTIONNEE = 'PRESELECTIONNEE';
    public const STATUT_RETENUE = 'RETENUE';

    protected $casts = [
        'id'                => 'integer',
        'candidat_id'       => 'integer',
        'offre_emploi_id'   => 'integer',
        'date_candidature'  => 'datetime',
    ];
}
