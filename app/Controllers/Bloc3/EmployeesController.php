<?php

namespace App\Controllers\Bloc3;

use App\Controllers\BaseController;
use App\Models\EmployeModel;
use App\Services\Bloc3\EmployeesService;

class EmployeesController extends BaseController
{
    private EmployeesService $employeesService;

    public function __construct()
    {
        $this->employeesService = new EmployeesService();
    }

    public function index()
    {
        $employeeModel = new EmployeModel();
        $db = db_connect();

        $rows = $employeeModel
            ->select([
                'employe.id AS employe_id',
                'employe.matricule',
                'candidat.nom',
                'candidat.prenom',
                'offre_emploi.titre AS poste',
                'departement.nom_departement AS departement',
                'employe.date_embauche',
                'affectation.date_debut AS affectation_date',
            ])
            ->join('candidat', 'candidat.id = employe.candidat_id', 'left')
            ->join('candidature', 'candidature.candidat_id = employe.candidat_id', 'left')
            ->join('proposition_embauche', 'proposition_embauche.candidature_id = candidature.id AND proposition_embauche.reponse = \'ACCEPTEE\'', 'left')
            ->join('offre_emploi', 'offre_emploi.id = candidature.offre_emploi_id', 'left')
            ->join('departement', 'departement.id = employe.departement_id', 'left')
            ->join('affectation', 'affectation.employe_id = employe.id AND affectation.date_fin IS NULL', 'left')
            ->orderBy('employe.date_embauche', 'DESC')
            ->asArray()
            ->findAll();

        $employees = array_map(static function (array $row): array {
            return [
                'id' => (int) ($row['employe_id'] ?? 0),
                'matricule' => $row['matricule'] ?? '—',
                'nom' => $row['nom'] ?? '',
                'prenom' => $row['prenom'] ?? '',
                'poste' => $row['poste'] ?? '—',
                'departement' => $row['departement'] ?? '—',
                'date_embauche' => $row['date_embauche'] ?? null,
                'statut' => ! empty($row['affectation_date']) ? 'Affectation initiale validée' : 'Dossier salarié créé',
            ];
        }, $rows);

        $eligibleCandidates = $this->employeesService->eligibleCandidates();
        $candidateDetails = [];
        foreach ($eligibleCandidates as $candidate) {
            $candidateDetails[(string) ($candidate['candidate_id'] ?? 0)] = [
                'nom' => $candidate['nom'],
                'prenom' => $candidate['prenom'],
                'email' => $candidate['email'],
                'telephone' => $candidate['telephone'] ?? '',
            ];
        }

        return view('bloc3/employees', [
            'employees' => $employees,
            'eligibleCandidates' => $eligibleCandidates,
            'candidateOptions' => $this->employeesService->candidateOptions(),
            'candidateDetails' => $candidateDetails,
            'departmentOptions' => $this->employeesService->departmentOptions(),
        ]);
    }

    public function save()
    {
        $candidateId = (int) ($this->request->getPost('candidate') ?? 0);
        $photoFile = $this->request->getFile('photo');

        $result = $this->employeesService->createEmployeeFromCandidate($candidateId, [
            'nom' => trim((string) ($this->request->getPost('nom') ?? '')),
            'prenom' => trim((string) ($this->request->getPost('prenom') ?? '')),
            'email' => trim((string) ($this->request->getPost('email') ?? '')),
            'date_naissance' => trim((string) ($this->request->getPost('date_naissance') ?? '')),
            'numero_cni' => trim((string) ($this->request->getPost('numero_cni') ?? '')),
            'date_delivrance_cni' => trim((string) ($this->request->getPost('date_delivrance_cni') ?? '')),
            'lieu_delivrance_cni' => trim((string) ($this->request->getPost('lieu_delivrance_cni') ?? '')),
            'telephone' => trim((string) ($this->request->getPost('telephone') ?? '')),
            'adresse' => trim((string) ($this->request->getPost('adresse') ?? '')),
            'situation_familiale' => trim((string) ($this->request->getPost('situation_familiale') ?? '')),
            'date_embauche' => trim((string) ($this->request->getPost('date_embauche') ?? '')),
            'departement_id' => (int) ($this->request->getPost('departement_id') ?? 0),
        ], $photoFile);

        if ($result['ok'] !== true) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }

        return redirect()->to('bloc3/employees')->with('success', $result['message']);
    }
}
