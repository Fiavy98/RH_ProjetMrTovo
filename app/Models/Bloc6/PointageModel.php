<?php

namespace App\Models\Bloc6;

use App\Models\SirhModel;

/**
 * Modèle pour la table `pointage`.
 * Enregistrement des entrées, sorties réelles, retards et heures supplémentaires.
 */
class PointageModel extends SirhModel
{
    protected $table = 'pointage';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'employe_id',
        'date_pointage',
        'heure_entree',
        'heure_sortie',
        'pause_minutes',
        'temps_travaille_heures',
        'retard_minutes',
        'heures_supplementaires',
        'statut',
        'motif_correction',
        'cree_le',
        'modifie_par',
    ];

    protected $validationRules = [
        'employe_id'             => 'required|is_natural_no_zero',
        'date_pointage'          => 'required|valid_date[Y-m-d]',
        'heure_entree'           => 'permit_empty',
        'heure_sortie'           => 'permit_empty',
        'pause_minutes'          => 'permit_empty|is_natural',
        'temps_travaille_heures' => 'permit_empty|decimal',
        'retard_minutes'         => 'permit_empty|integer',
        'heures_supplementaires' => 'permit_empty|decimal',
        'statut'                 => 'permit_empty|max_length[30]',
        'motif_correction'       => 'permit_empty',
    ];
}

