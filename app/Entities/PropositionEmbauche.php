<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `proposition_embauche`.
 * Origine : Bloc 3.
 *
 * Les constantes ci-dessous reprennent les valeurs de la base ; si le vocabulaire des
 * statuts change (majuscules/minuscules, nouveaux statuts), on ne modifie qu'ici.
 */
class PropositionEmbauche extends SirhEntity
{
    public const REPONSE_EN_ATTENTE = 'EN_ATTENTE';
    public const REPONSE_ACCEPTEE = 'ACCEPTEE';
    public const REPONSE_REFUSEE = 'REFUSEE';

    protected $casts = [
        'id'                => 'integer',
        'candidature_id'    => 'integer',
        'salaire'           => 'float',
        'type_contrat_id'   => 'integer',
        'date_debut'        => 'datetime',
        'date_proposition'  => 'datetime',
        'date_reponse'      => '?datetime',
    ];
}
