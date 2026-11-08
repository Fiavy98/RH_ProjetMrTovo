<?php

namespace App\Models\Bloc7;

use App\Models\SirhModel;

/** Historique append-only des validations de congé. */
class ValidationDemandeCongeModel extends SirhModel
{
    protected $table = 'validation_demande_conge';
    protected $allowedFields = ['demande_conge_id', 'validateur_employe_id', 'etape', 'numero_tentative', 'decision', 'commentaire'];
    protected $validationRules = ['demande_conge_id'=>'required|is_natural_no_zero','validateur_employe_id'=>'required|is_natural_no_zero','etape'=>'required|in_list[manager,rh]','numero_tentative'=>'required|is_natural_no_zero','decision'=>'required|in_list[approuvee,refusee,a_corriger]','commentaire'=>'permit_empty'];
}
