<?php

namespace App\Services\Bloc1;

use App\Models\DemandeModel;
use App\Models\DepartementModel;
use App\Models\EmployeModel;
use App\Models\PosteModel;
use App\Models\StatusModel;
use App\Models\ValidationModel;

class DemandeDirectionService
{
    public function departments(): array
    {
        return (new DepartementModel())->orderBy('nom_departement', 'ASC')->findAll();
    }

    public function requests(): array
    {
        return $this->query()
            ->where('status.code', 'DIRECTION_attente')
            ->orderBy('demande.date_demande', 'DESC')
            ->findAll();
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

        if ($request === null || $request->status_code !== 'DIRECTION_attente') {
            return ['ok' => false, 'message' => 'Cette demande n’est plus disponible pour la Direction.'];
        }
        if (! in_array($decision, ['valider', 'refuser'], true) || $comment === '') {
            return ['ok' => false, 'message' => 'La décision et le commentaire Direction sont obligatoires.'];
        }

        $nextCode = $decision === 'valider' ? 'DIRECTION_validee' : 'DIRECTION_refusee';
        $status = (new StatusModel())->where('actif', true)->where('code', $nextCode)->first();
        $direction = (new EmployeModel())
            ->select('employe.*')
            ->join('role', 'role.id = employe.role_id')
            ->where('employe.actif', true)
            ->where("UPPER(TRIM(role.nom_role)) = 'DIRECTION'", null, false)
            ->orderBy('employe.id')
            ->first();

        if ($status === null) {
            return ['ok' => false, 'message' => 'Le statut requis pour cette décision Direction est introuvable ou inactif.'];
        }
        if ($direction === null) {
            return ['ok' => false, 'message' => 'Aucun validateur Direction actif n’est configuré.'];
        }
        if ((new DemandeModel())->update($id, ['status_id' => $status->id]) === false) {
            return ['ok' => false, 'message' => 'La demande n’a pas pu être mise à jour.'];
        }
        if ((new ValidationModel())->insert([
            'demande_id' => $id,
            'validateur_id' => $direction->id,
            'status_id' => $status->id,
            'commentaire' => $comment,
            'date_validation' => date('Y-m-d H:i:s'),
        ]) === false) {
            return ['ok' => false, 'message' => 'La décision Direction n’a pas pu être enregistrée.'];
        }

        return ['ok' => true, 'message' => 'La décision Direction a été enregistrée.'];
    }

    public function createPosts(int $id, array $input): array
    {
        $request = $this->request($id);
        if ($request === null || $request->status_code !== 'DIRECTION_validee') {
            return ['ok' => false, 'message' => 'Cette demande n’est pas approuvée par la Direction.'];
        }

        $title = trim((string) ($input['intitule'] ?? ''));
        $departmentId = (int) ($input['departement_id'] ?? $request->departement_id);
        $description = trim((string) ($input['description'] ?? ''));
        $skills = trim((string) ($input['competences_requises'] ?? ''));
        if ($title === '' || $departmentId < 1) {
            return ['ok' => false, 'message' => 'L’intitulé et le département du poste sont obligatoires.'];
        }
        if ((new DepartementModel())->find($departmentId) === null) {
            return ['ok' => false, 'message' => 'Le département sélectionné est introuvable.'];
        }

        $activeStatus = (new StatusModel())->where('actif', true)->where('code', 'ACTIF')->first();
        if ($activeStatus === null) {
            return ['ok' => false, 'message' => 'Le statut ACTIF est introuvable ou inactif.'];
        }

        $postModel = new PosteModel();
        $existing = $postModel->where('demande_id', $id)->countAllResults();
        $count = max(1, (int) $request->nombre_postes);
        if ($existing >= $count) {
            return ['ok' => false, 'message' => 'Les postes de cette demande ont déjà été créés.'];
        }

        for ($index = $existing + 1; $index <= $count; $index++) {
            $postTitle = $count > 1 ? $title . '_' . $index : $title;
            if ($postModel->insert([
                'intitule' => $postTitle,
                'description' => $description,
                'competences_requises' => $skills,
                'demande_id' => $id,
                'departement_id' => $departmentId,
                'status_id' => $activeStatus->id,
            ]) === false) {
                return ['ok' => false, 'message' => 'La création du poste a échoué.'];
            }
        }

        return ['ok' => true, 'message' => 'Le ou les postes ont été créés et associés à la demande.'];
    }

    private function query()
    {
        return (new DemandeModel())
            ->select('demande.*, employe.nom AS demandeur_nom, employe.prenom AS demandeur_prenom, departement.nom_departement, status.code AS status_code, status.libelle AS status_libelle')
            ->join('employe', 'employe.id = demande.demandeur_id')
            ->join('departement', 'departement.id = demande.departement_id')
            ->join('status', 'status.id = demande.status_id');
    }
}
