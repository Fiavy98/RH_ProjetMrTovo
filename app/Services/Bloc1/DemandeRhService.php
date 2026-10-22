<?php

namespace App\Services\Bloc1;

use App\Models\DemandeModel;
use App\Models\EmployeModel;
use App\Models\StatusModel;
use App\Models\ValidationModel;

class DemandeRhService
{
    public function requests(): array
    {
        return $this->query()->where('status.code', 'RH_attente')->orderBy('demande.date_demande', 'DESC')->findAll();
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
        $min = trim((string) ($input['budget_min'] ?? ''));
        $max = trim((string) ($input['budget_max'] ?? ''));

        if ($request === null || $request->status_code !== 'RH_attente') {
            return ['ok' => false, 'message' => 'Cette demande n’est plus disponible pour validation RH.'];
        }
        if (! in_array($decision, ['valider', 'refuser'], true) || $comment === '') {
            return ['ok' => false, 'message' => 'La décision et le commentaire RH sont obligatoires.'];
        }
        if ($min === '' || $max === '' || ! is_numeric($min) || ! is_numeric($max)
            || (float) $min < 0 || (float) $max < 0 || (float) $min > (float) $max) {
            return ['ok' => false, 'message' => 'Le budget prévisionnel RH est obligatoire et doit être cohérent.'];
        }

        return $this->saveDecision(
            $request,
            $decision === 'valider' ? 'FINANCE_attente' : 'RH_refusee',
            $decision === 'valider' ? 'RH_validee' : 'RH_refusee',
            $min,
            $max,
            $comment
        );
    }

    private function saveDecision(object $request, string $statusCode, string $decisionCode, string $min, string $max, string $comment): array
    {
        $status = (new StatusModel())->where('actif', true)->where('code', $statusCode)->first();
        $decisionStatus = (new StatusModel())->where('actif', true)->where('code', $decisionCode)->first();
        $rh = (new EmployeModel())
            ->select('employe.*')
            ->join('role', 'role.id = employe.role_id')
            ->where('employe.actif', true)
            ->where('role.nom_role', 'RH')
            ->orderBy('employe.id')
            ->first();
        if ($status === null || $decisionStatus === null || $rh === null) {
            return ['ok' => false, 'message' => 'Le statut ou le validateur RH est introuvable.'];
        }

        $model = new DemandeModel();
        if (! $model->update($request->id, ['budget_min' => $min, 'budget_max' => $max, 'status_id' => $status->id])) {
            return ['ok' => false, 'message' => 'La demande n’a pas pu être mise à jour.'];
        }
        if ((new ValidationModel())->insert([
            'demande_id' => $request->id, 'validateur_id' => $rh->id, 'status_id' => $decisionStatus->id,
            'commentaire' => $comment, 'date_validation' => date('Y-m-d H:i:s'),
        ]) === false) {
            return ['ok' => false, 'message' => 'La décision RH n’a pas pu être enregistrée.'];
        }

        return ['ok' => true, 'message' => $statusCode === 'FINANCE_attente'
            ? 'La demande est transmise à la Finance.' : 'La demande a été refusée par les RH.'];
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
