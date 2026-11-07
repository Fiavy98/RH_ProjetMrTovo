<?php

namespace App\Models\Bloc5;

use App\Models\SirhModel;

/**
 * Modèle pour la table `audit_dossier`.
 * Traçabilité des consultations et modifications des données sensibles du dossier salarié.
 */
class AuditDossierModel extends SirhModel
{
    protected $table = 'audit_dossier';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'utilisateur_id',
        'employe_cible_id',
        'action',
        'categorie_donnee',
        'details',
        'statut_acces',
        'ip_address',
        'date_action',
    ];

    protected $validationRules = [
        'utilisateur_id'   => 'required|is_natural_no_zero',
        'employe_cible_id' => 'required|is_natural_no_zero',
        'action'           => 'required|max_length[60]',
        'categorie_donnee' => 'required|max_length[50]',
        'statut_acces'     => 'permit_empty|max_length[20]',
    ];
}

