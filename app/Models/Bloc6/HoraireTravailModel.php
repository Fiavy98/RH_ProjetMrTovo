<?php

namespace App\Models\Bloc6;

use App\Models\SirhModel;

/**
 * Modèle pour la table `horaire_travail`.
 * Référentiel des régimes et horaires de travail.
 */
class HoraireTravailModel extends SirhModel
{
    protected $table = 'horaire_travail';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'code',
        'libelle',
        'heure_debut',
        'heure_fin',
        'pause_minutes',
        'heures_jour',
        'heures_hebdo',
        'description',
        'actif',
    ];

    protected $validationRules = [
        'code'          => 'required|max_length[30]',
        'libelle'       => 'required|max_length[100]',
        'heure_debut'   => 'required',
        'heure_fin'     => 'required',
        'pause_minutes' => 'permit_empty|is_natural',
        'heures_jour'   => 'permit_empty|decimal',
        'heures_hebdo'  => 'permit_empty|decimal',
        'actif'         => 'permit_empty',
    ];
}

