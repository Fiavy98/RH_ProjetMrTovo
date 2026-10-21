<?php

namespace App\Entities;

/**
 * Entité : une ligne de la table `historique_recrutement`.
 * Origine : Bloc 2 (journal propre au recrutement).
 *
 * Les colonnes non texte sont typées dans $casts (la base renvoie des chaînes).
 */
class HistoriqueRecrutement extends SirhEntity
{
    protected $casts = [
        'id'              => 'integer',
        'entite_id'       => 'integer',
        'utilisateur_id'  => '?integer',
        'date_action'     => 'datetime',
    ];
}
