<?php

namespace App\Models\Bloc7;

use App\Models\SirhModel;

/** Modèle squelette pour les soldes de congés. */
class SoldeCongeModel extends SirhModel
{
    protected $table = 'solde_conge';
    protected $allowedFields = ['employe_id', 'contrat_id', 'type_conge_id', 'quantite_acquise', 'quantite_consommee', 'quantite_reservee', 'calcule_le'];
    protected $validationRules = ['employe_id' => 'required|is_natural_no_zero', 'contrat_id' => 'required|is_natural_no_zero', 'type_conge_id' => 'required|is_natural_no_zero', 'quantite_acquise' => 'required|decimal', 'quantite_consommee' => 'required|decimal', 'quantite_reservee' => 'required|decimal'];
}
