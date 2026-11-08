<?php

namespace App\Models\Bloc7;

use App\Models\SirhModel;

/** Modèle squelette pour le référentiel des types de congé. */
class TypeCongeModel extends SirhModel
{
    /** Référentiel partagé des types de congé. */
    protected $table = 'type_conge';
    protected $returnType = 'array';
    protected $allowedFields = [
        'code', 'libelle', 'description', 'actif', 'justificatif_obligatoire',
        'mode_decompte', 'cree_le', 'modifie_le', 'cree_par', 'modifie_par',
    ];
}
