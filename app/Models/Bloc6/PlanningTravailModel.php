<?php

namespace App\Models\Bloc6;

use App\Models\SirhModel;

/**
 * Modèle pour la table `planning_travail`.
 * Horaires planifiés par salarié et par jour.
 */
class PlanningTravailModel extends SirhModel
{
    protected $table = 'planning_travail';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'employe_id',
        'date_travail',
        'horaire_id',
        'heure_debut_prevue',
        'heure_fin_prevue',
        'pause_minutes_prevue',
        'heures_prevues',
        'est_jour_repos',
        'cree_le',
        'cree_par',
    ];

    protected $validationRules = [
        'employe_id'           => 'required|is_natural_no_zero',
        'date_travail'         => 'required|valid_date[Y-m-d]',
        'horaire_id'           => 'permit_empty|is_natural_no_zero',
        'heure_debut_prevue'   => 'required',
        'heure_fin_prevue'     => 'required',
        'pause_minutes_prevue' => 'permit_empty|is_natural',
        'heures_prevues'       => 'permit_empty|decimal',
        'est_jour_repos'       => 'permit_empty',
    ];
}

