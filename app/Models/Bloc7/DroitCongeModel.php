<?php

namespace App\Models\Bloc7;

use App\Models\SirhModel;

/** Modèle squelette pour les droits attribués par salarié et période. */
class DroitCongeModel extends SirhModel
{
    protected $table = 'droit_conge';
    protected $allowedFields = ['employe_id', 'contrat_id', 'type_conge_id', 'date_acquisition', 'date_expiration', 'quantite', 'nature', 'origine', 'commentaire', 'attribue_par'];
    protected $validationRules = ['employe_id' => 'required|is_natural_no_zero', 'contrat_id' => 'required|is_natural_no_zero', 'type_conge_id' => 'required|is_natural_no_zero', 'date_acquisition' => 'required|valid_date[Y-m-d]', 'date_expiration' => 'required|valid_date[Y-m-d]', 'quantite' => 'required|decimal', 'nature' => 'required|in_list[acquisition,regularisation]', 'origine' => 'permit_empty|max_length[100]', 'commentaire' => 'permit_empty', 'attribue_par' => 'permit_empty|is_natural_no_zero'];
}
