<?php

namespace App\Models\Bloc7;

use App\Models\SirhModel;

/** Modèle squelette pour les absences et leurs justificatifs. */
class AbsenceModel extends SirhModel
{
    /** Registre des absences et métadonnées des justificatifs stockés hors du webroot. */
    protected $table = 'absence';
    protected $returnType = 'array';
    protected $allowedFields = [
        'employe_id', 'type_conge_id', 'demande_conge_id', 'date_debut', 'date_fin',
        'motif', 'commentaire', 'justifiee', 'justificatif_nom_original',
        'justificatif_nom_stocke', 'justificatif_mime', 'justificatif_taille',
        'statut', 'cree_le', 'modifie_le', 'cree_par', 'modifie_par',
    ];
}
