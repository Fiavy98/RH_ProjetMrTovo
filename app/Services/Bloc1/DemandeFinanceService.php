<?php

namespace App\Services\Bloc1;

use App\Models\DemandeModel;
use App\Models\EmployeModel;
use App\Models\StatusModel;
use App\Models\ValidationModel;

class DemandeFinanceService
{
    public function requests(): array
    {
        return $this->query()->where('status.code', 'FINANCE_attente')->orderBy('demande.date_demande', 'DESC')->findAll();
    }

    public function request(int $id): ?object
    {
        return $this->query()->find($id);
    }

    public function decide(int $id, array $input): array
    {
        $request = $this->request($id);
        $decision = (string) ($input['decision'] ?? '');
        $comment = trim((string) ($input['commentaire'] ?? ''));
        if ($request === null || $request->status_code !== 'FINANCE_attente') {
            return ['ok' => false, 'message' => 'Cette demande n’est plus disponible pour la Finance.'];
        }
        if (! in_array($decision, ['valider', 'refuser', 'complement'], true) || $comment === '') {
            return ['ok' => false, 'message' => 'L’avis et le commentaire Finance sont obligatoires.'];
        }

        $next = ['valider' => 'DIRECTION_attente', 'refuser' => 'FINANCE_refusee', 'complement' => 'FINANCE_validation'][$decision];
        $decisionCode = ['valider' => 'FINANCE_validee', 'refuser' => 'FINANCE_refusee', 'complement' => 'FINANCE_validation'][$decision];
        $status = (new StatusModel())->where('actif', true)->where('code', $next)->first();
        $decisionStatus = (new StatusModel())->where('actif', true)->where('code', $decisionCode)->first();
        $finance = (new EmployeModel())
            ->select('employe.*')
            ->join('role', 'role.id = employe.role_id')
            ->where('employe.actif', true)
            ->where("UPPER(TRIM(role.nom_role)) = 'FINANCE'", null, false)
            ->orderBy('employe.id')
            ->first();
        if ($status === null || $decisionStatus === null) {
            return ['ok' => false, 'message' => 'Le statut requis pour cette décision Finance est introuvable ou inactif.'];
        }
        if ($finance === null) {
            return ['ok' => false, 'message' => 'Aucun validateur Finance actif n’est configuré.'];
        }
        if ((new DemandeModel())->update($id, ['status_id' => $status->id]) === false) {
            return ['ok' => false, 'message' => 'La demande n’a pas pu être mise à jour.'];
        }
        if ((new ValidationModel())->insert([
            'demande_id' => $id, 'validateur_id' => $finance->id, 'status_id' => $decisionStatus->id,
            'commentaire' => $comment, 'date_validation' => date('Y-m-d H:i:s'),
        ]) === false) {
            return ['ok' => false, 'message' => 'L’avis Finance n’a pas pu être enregistré.'];
        }
        return ['ok' => true, 'message' => 'L’avis Finance a été enregistré.'];
    }

    private function query()
    {
        return (new DemandeModel())
            ->select('demande.*, employe.nom AS demandeur_nom, employe.prenom AS demandeur_prenom, departement.nom_departement, status.code AS status_code')
            ->join('employe', 'employe.id = demande.demandeur_id')
            ->join('departement', 'departement.id = demande.departement_id')
            ->join('status', 'status.id = demande.status_id');
    }
}
