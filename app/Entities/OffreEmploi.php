<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `offre_emploi`.
 * Origine : Bloc 2.
 *
 * Les constantes ci-dessous reprennent les valeurs de la base ; si le vocabulaire des
 * statuts change (majuscules/minuscules, nouveaux statuts), on ne modifie qu'ici.
 */
class OffreEmploi extends SirhEntity
{
    public const STATUT_BROUILLON = 'BROUILLON';
    public const STATUT_PUBLIEE = 'PUBLIEE';
    public const STATUT_CLOTUREE = 'CLOTUREE';

    protected $casts = [
        'id'                      => 'integer',
        'demande_recrutement_id'  => '?integer',
        'poste_id'                => '?integer',
        'date_publication'        => '?datetime',
        'date_cloture'            => '?datetime',
    ];
}
