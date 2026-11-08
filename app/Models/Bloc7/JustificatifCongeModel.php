<?php

namespace App\Models\Bloc7;

use App\Models\SirhModel;

/** Métadonnées de pièces conservées dans writable, jamais exposées directement. */
class JustificatifCongeModel extends SirhModel
{
    protected $table = 'justificatif_conge';
    protected $allowedFields = ['demande_conge_id','ajoute_par_employe_id','nom_fichier','chemin_stockage','type_mime','taille_octets'];
    protected $validationRules = ['demande_conge_id'=>'required|is_natural_no_zero','ajoute_par_employe_id'=>'required|is_natural_no_zero','nom_fichier'=>'required|max_length[255]','chemin_stockage'=>'required|max_length[500]','type_mime'=>'permit_empty|max_length[100]','taille_octets'=>'permit_empty|is_natural'];
}
