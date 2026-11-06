<?php

namespace App\Models;

use App\Entities\HistoriqueSalaire;

/**
 * Accès à la table `historique_salaire`.
 */
class HistoriqueSalaireModel extends SirhModel
{
    protected $table      = 'historique_salaire';
    protected $returnType = HistoriqueSalaire::class;

    protected $allowedFields = [
        'employe_id',
        'ancien_salaire',
        'nouveau_salaire',
        'date_effet',
        'motif',
    ];

    protected $validationRules = [
        'employe_id'      => 'required|is_natural_no_zero',
        'ancien_salaire'  => 'permit_empty|numeric',
        'nouveau_salaire' => 'required|numeric',
        'date_effet'      => 'required|valid_date[Y-m-d]',
        'motif'           => 'permit_empty',
    ];
}
