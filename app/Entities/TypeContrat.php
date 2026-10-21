<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `type_contrat`.
 * Origine : Référentiel partagé (Blocs 3 et 4) : table de paramètres des contrats.
 *
 * Les constantes ci-dessous reprennent les valeurs de la base ; si le vocabulaire des
 * statuts change (majuscules/minuscules, nouveaux statuts), on ne modifie qu'ici.
 */
class TypeContrat extends SirhEntity
{
    public const CODE_CE = 'CE';
    public const CODE_CDD = 'CDD';
    public const CODE_CDI = 'CDI';

    protected $casts = [
        'id'                      => 'integer',
        'duree_max_mois'          => '?integer',
        'nb_renouvellements_max'  => 'integer',
    ];
}
