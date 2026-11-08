<?php

namespace App\Models\Bloc7;

use App\Models\SirhModel;

/** Calendrier des jours fériés appliqué au décompte en jours ouvrés. */
class JourFerieModel extends SirhModel
{
    protected $table = 'jour_ferie';
    protected $primaryKey = 'date_ferie';
    protected $useAutoIncrement = false;
    protected $allowedFields = ['date_ferie','libelle','actif'];
    protected $validationRules = ['date_ferie'=>'required|valid_date[Y-m-d]','libelle'=>'required|max_length[150]','actif'=>'permit_empty'];
}
