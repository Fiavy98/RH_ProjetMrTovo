<?php

namespace App\Services\Bloc1;

use App\Models\PosteModel;

class PosteService
{
    public function positions(): array
    {
        return (new PosteModel())
            ->select('poste.*, demande.intitule AS demande_intitule, demande.id AS demande_id_ref, departement.nom_departement, status.code AS status_code, status.libelle AS status_libelle')
            ->join('demande', 'demande.id = poste.demande_id')
            ->join('departement', 'departement.id = poste.departement_id')
            ->join('status', 'status.id = poste.status_id')
            ->orderBy('poste.id', 'DESC')
            ->findAll();
    }
}
