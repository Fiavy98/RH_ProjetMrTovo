<?php

namespace App\Services\Bloc1;

use App\Models\DepartementModel;
use App\Models\DemandeModel;
use App\Models\EmployeModel;
use App\Models\StatusModel;
use App\Models\TypeContratModel;
use App\Services\BlocPageService;

/** Écrans de démonstration du Bloc 1 : expression du besoin et validations. */
class DemandeService extends BlocPageService
{
    public function screens(): array
    {
        return $this->defineScreens([
            'dashboard' => 'Vue des demandes', 'requests' => 'Mes demandes', 'demande-form' => 'Nouvelle demande',
            'request-check' => 'Consulter une demande',
            'rh-review' => 'Analyse RH', 'finance-review' => 'Avis Finance',
            'direction-review' => 'Décisions Direction', 'positions' => 'Postes associés',
            'request-history' => 'Historique des demandes',
        ], 'Bloc 1 · Expression du besoin');
    }

    public function allRequests(): array
    {
        return (new DemandeModel())
            ->select('demande.*, demandeur.nom AS demandeur_nom, demandeur.prenom AS demandeur_prenom, departement.nom_departement, status.code AS status_code, status.libelle AS status_libelle, type_contrat.libelle AS type_contrat_libelle')
            ->join('employe demandeur', 'demandeur.id = demande.demandeur_id')
            ->join('departement', 'departement.id = demande.departement_id')
            ->join('status', 'status.id = demande.status_id')
            ->join('type_contrat', 'type_contrat.id = demande.type_contrat_id', 'left')
            ->orderBy('demande.date_demande', 'DESC')
            ->findAll();
    }

    /** @return array{departments: list<object>, contracts: list<object>, employee: ?object, formReady: bool, formError: string} */
    public function formData(): array
    {
        $departments = (new DepartementModel())->orderBy('nom_departement', 'ASC')->findAll();
        $contracts = (new TypeContratModel())->orderBy('libelle', 'ASC')->findAll();
        $employee = (new EmployeModel())
            ->where('actif', true)
            ->orderBy('id', 'ASC')
            ->first();
        $status = (new StatusModel())
            ->where('actif', true)
            ->where('code', 'RH_attente')
            ->first();

        $formError = $employee === null
            ? 'Aucun employé actif n’est disponible pour créer la demande.'
            : ($status === null
                ? 'Le statut RH_attente doit être présent dans la table status.'
                : ($departments === [] ? 'Aucun département n’est disponible.' : ($contracts === [] ? 'Aucun type de contrat n’est disponible.' : '')));

        return [
            'departments' => $departments,
            'contracts' => $contracts,
            'employee' => $employee,
            'formReady' => $employee !== null && $status !== null && $departments !== [] && $contracts !== [],
            'formError' => $formError,
        ];
    }

    public function save(string $slug, array $input): array
    {
        if ($slug !== 'demande-form') {
            return parent::save($slug, $input);
        }

        $employee = (new EmployeModel())
            ->where('actif', true)
            ->orderBy('id', 'ASC')
            ->first();
        $status = (new StatusModel())
            ->where('actif', true)
            ->where('code', 'RH_attente')
            ->first();
        $departmentId = (int) ($input['departement_id'] ?? 0);
        $department = $departmentId > 0 ? (new DepartementModel())->find($departmentId) : null;
        $contractId = (int) ($input['type_contrat_id'] ?? 0);
        $contract = $contractId > 0 ? (new TypeContratModel())->find($contractId) : null;
        $motif = trim((string) ($input['motif'] ?? ''));
        $budgetMin = trim((string) ($input['budget_min'] ?? ''));
        $budgetMax = trim((string) ($input['budget_max'] ?? ''));
        $intitule = trim((string) ($input['intitule'] ?? ''));
        $nombrePostes = (int) ($input['nombre_postes'] ?? 0);

        if ($employee === null || $status === null || $department === null || $contract === null) {
            return ['ok' => false, 'message' => 'Le demandeur, le statut, le département ou le type de contrat est invalide.'];
        }
        if ($motif === '') {
            return ['ok' => false, 'message' => 'Le motif et la justification sont obligatoires.'];
        }
        if ($intitule === '' || mb_strlen($intitule) > 150) {
            return ['ok' => false, 'message' => 'L’intitulé du poste est obligatoire et limité à 150 caractères.'];
        }
        if ($nombrePostes < 1) {
            return ['ok' => false, 'message' => 'Le nombre de postes doit être supérieur à zéro.'];
        }
        if (($budgetMin !== '' && (! is_numeric($budgetMin) || (float) $budgetMin < 0))
            || ($budgetMax !== '' && (! is_numeric($budgetMax) || (float) $budgetMax < 0))) {
            return ['ok' => false, 'message' => 'Les budgets doivent être des montants positifs ou laissés vides.'];
        }
        if ($budgetMin !== '' && $budgetMax !== '' && (float) $budgetMin > (float) $budgetMax) {
            return ['ok' => false, 'message' => 'Le budget minimum ne peut pas dépasser le budget maximum.'];
        }

        $model = new DemandeModel();
        $id = $model->insert([
            'intitule' => $intitule,
            'nombre_postes' => $nombrePostes,
            'motif' => $motif,
            'budget_min' => $budgetMin === '' ? null : $budgetMin,
            'budget_max' => $budgetMax === '' ? null : $budgetMax,
            'type_contrat_id' => $contract->id,
            'status_id' => $status->id,
            'demandeur_id' => $employee->id,
            'departement_id' => $department->id,
        ]);

        if ($id === false) {
            return ['ok' => false, 'message' => implode(' ', $model->errors()) ?: 'La demande n’a pas pu être enregistrée.'];
        }

        return ['ok' => true, 'message' => 'La demande a été enregistrée.'];
    }

}
