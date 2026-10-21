<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `contrat`.
 * Origine : Bloc 4.
 *
 * Les constantes ci-dessous reprennent les valeurs de la base ; si le vocabulaire des
 * statuts change (majuscules/minuscules, nouveaux statuts), on ne modifie qu'ici.
 */
class Contrat extends SirhEntity
{
    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_SIGNE = 'signe';
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_TERMINE = 'termine';
    public const STATUT_ANNULE = 'annule';

    protected $casts = [
        'id'                    => 'integer',
        'employe_id'            => 'integer',
        'type_contrat_id'       => 'integer',
        'contrat_precedent_id'  => '?integer',
        'salaire'               => 'float',
        'date_debut'            => 'datetime',
        'date_fin'              => '?datetime',
        'date_signature'        => '?datetime',
        'cree_le'               => 'datetime',
        'cree_par'              => '?integer',
    ];
}
