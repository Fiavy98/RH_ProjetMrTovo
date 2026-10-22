<?php

namespace App\Services\Bloc1;

use App\Models\DemandeModel;
use App\Models\ValidationModel;

class DemandeHistoryService
{
    public function requests(): array
    {
        return (new DemandeModel())
            ->select('demande.*, status.code AS status_code, status.libelle AS status_libelle')
            ->join('status', 'status.id = demande.status_id')
            ->orderBy('demande.date_demande', 'DESC')
            ->findAll();
    }

    public function request(int $id): ?object
    {
        return (new DemandeModel())
            ->select('demande.*, demandeur.nom AS demandeur_nom, demandeur.prenom AS demandeur_prenom, departement.nom_departement, status.code AS status_code, status.libelle AS status_libelle')
            ->join('employe demandeur', 'demandeur.id = demande.demandeur_id')
            ->join('departement', 'departement.id = demande.departement_id')
            ->join('status', 'status.id = demande.status_id')
            ->find($id);
    }

    public function validations(int $id): array
    {
        return (new ValidationModel())
            ->select("validation.*, acteur.nom AS acteur_nom, acteur.prenom AS acteur_prenom, status.code AS status_code, CASE
                WHEN status.code LIKE 'RH_%' THEN 'RH'
                WHEN status.code LIKE 'FINANCE_%' THEN 'Finance'
                WHEN status.code LIKE 'DIRECTION_%' THEN 'Direction'
                ELSE status.libelle
            END AS etape, status.libelle AS decision")
            ->join('employe acteur', 'acteur.id = validation.validateur_id')
            ->join('status', 'status.id = validation.status_id')
            ->where('validation.demande_id', $id)
            ->orderBy('validation.date_validation', 'ASC')
            ->findAll();
    }
}
