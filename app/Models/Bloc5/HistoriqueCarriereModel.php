<?php

namespace App\Models\Bloc5;

use App\Models\SirhModel;

/** Modèle à raccorder au schéma des événements de carrière et de rémunération. */
class HistoriqueCarriereModel extends SirhModel
{
    // TODO Bloc 5 : confirmer le nom de la table et les champs après conception du schéma.
    protected $table = 'historique_carriere';
    protected $allowedFields = [];
}
