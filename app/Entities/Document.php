<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `document`.
 * Origine : Bloc 4.
 *
 * Les constantes ci-dessous reprennent les valeurs de la base ; si le vocabulaire des
 * statuts change (majuscules/minuscules, nouveaux statuts), on ne modifie qu'ici.
 */
class Document extends SirhEntity
{
    public const TYPE_CONTRAT_SIGNE = 'contrat_signe';
    public const TYPE_PIECE_IDENTITE = 'piece_identite';
    public const TYPE_AUTRE = 'autre';

    protected $casts = [
        'id'             => 'integer',
        'employe_id'     => 'integer',
        'contrat_id'     => '?integer',
        'taille_octets'  => '?integer',
        'cree_le'        => 'datetime',
        'cree_par'       => '?integer',
    ];
}
