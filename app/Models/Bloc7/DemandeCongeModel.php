<?php

namespace App\Models\Bloc7;

use App\Models\SirhModel;

/** Modèle squelette pour les demandes de congé et leurs décisions. */
class DemandeCongeModel extends SirhModel
{
    protected $table = 'demande_conge';
    protected $allowedFields = ['reference', 'employe_id', 'saisi_par_employe_id', 'contrat_id', 'type_conge_id', 'date_debut', 'date_fin', 'portion_debut', 'portion_fin', 'duree_demandee', 'unite_decompte', 'statut', 'motif', 'soumise_le', 'cloturee_le'];
    protected $validationRules = ['reference' => 'required|max_length[30]', 'employe_id' => 'required|is_natural_no_zero', 'saisi_par_employe_id' => 'required|is_natural_no_zero', 'contrat_id' => 'required|is_natural_no_zero', 'type_conge_id' => 'required|is_natural_no_zero', 'date_debut' => 'required|valid_date[Y-m-d]', 'date_fin' => 'required|valid_date[Y-m-d]', 'portion_debut' => 'required|in_list[journee,matin,apres_midi]', 'portion_fin' => 'required|in_list[journee,matin,apres_midi]', 'duree_demandee' => 'required|decimal', 'unite_decompte' => 'required|in_list[jours_ouvres]', 'statut' => 'required|in_list[brouillon,soumise,en_attente_manager,en_attente_rh,a_corriger,approuvee,refusee,retiree,annulee]', 'motif' => 'permit_empty', 'soumise_le' => 'permit_empty', 'cloturee_le' => 'permit_empty'];
}
