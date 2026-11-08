<?php

namespace App\Models\Bloc7;

use App\Models\SirhModel;

/** Audit des annulations effectuées par la RH après approbation. */
class AnnulationDemandeCongeModel extends SirhModel
{
    protected $table = 'annulation_demande_conge';
    protected $allowedFields = ['demande_conge_id','annule_par_employe_id','motif'];
    protected $validationRules = ['demande_conge_id'=>'required|is_natural_no_zero','annule_par_employe_id'=>'required|is_natural_no_zero','motif'=>'required'];
}
