<?php

namespace App\Controllers\Bloc3;

use App\Controllers\BaseController;
use App\Models\EmployeModel;

class EmployeesController extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $employeeModel = new EmployeModel();

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

        $acceptedCandidates = $db->table('proposition_embauche pe')
            ->select(['c.id AS candidate_id', 'c.nom', 'c.prenom', 'c.email'])
            ->join('candidature ca', 'ca.id = pe.candidature_id', 'left')
            ->join('candidat c', 'c.id = ca.candidat_id', 'left')
            ->where('pe.reponse', 'ACCEPTEE')
            ->whereIn('pe.decision_direction', ['VALIDEE', 'ACCEPTE', 'ACCEPTEE'])
            ->orderBy('c.nom', 'ASC')
            ->get()
            ->getResultArray();

        $alreadyUsedCandidateIds = array_map(
            static fn (array $row): int => (int) ($row['candidat_id'] ?? 0),
            $db->table('employe')->select('candidat_id')->get()->getResultArray()
        );

        $candidateOptions = ['' => 'Choisir un candidat'];
        $candidateDetails = [];

        foreach ($acceptedCandidates as $candidate) {
            $candidateId = (int) ($candidate['candidate_id'] ?? 0);
            if ($candidateId <= 0 || in_array($candidateId, $alreadyUsedCandidateIds, true)) {
                continue;
            }

            $candidateName = trim((string) ($candidate['nom'] ?? '') . ' ' . (string) ($candidate['prenom'] ?? ''));
            if ($candidateName === '') {
                continue;
            }

            $candidateOptions[(string) $candidateId] = $candidateName;
            $candidateDetails[(string) $candidateId] = [
                'nom' => (string) ($candidate['nom'] ?? ''),
                'prenom' => (string) ($candidate['prenom'] ?? ''),
                'email' => (string) ($candidate['email'] ?? ''),
            ];
        }

        $departmentRows = $db->table('departement')
            ->select(['id', 'nom_departement'])
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $departmentOptions = ['' => 'Choisir un département'];
        foreach ($departmentRows as $department) {
            $departmentId = (int) ($department['id'] ?? 0);
            if ($departmentId > 0) {
                $departmentOptions[(string) $departmentId] = (string) ($department['nom_departement'] ?? 'Département');
            }
        }

        return view('bloc3/employees', [
            'employees' => $employees,
            'candidateOptions' => $candidateOptions,
            'candidateDetails' => $candidateDetails,
            'departmentOptions' => $departmentOptions,
        ]);
    }

    public function save()
    {
        $db = db_connect();
        $candidateId = (int) ($this->request->getPost('candidate') ?? 0);
        $jobCandidate = $db->table('candidat')->where('id', $candidateId)->get()->getRowArray();

        if ($candidateId <= 0 || empty($jobCandidate)) {
            return redirect()->back()->withInput()->with('error', 'Veuillez choisir un candidat validé par la direction.');
        }

        $existingEmployee = $db->table('employe')->where('candidat_id', $candidateId)->get()->getRowArray();
        if (! empty($existingEmployee)) {
            return redirect()->back()->withInput()->with('error', 'Ce candidat est déjà enregistré comme salarié.');
        }

        $nom = trim((string) ($this->request->getPost('nom') ?? $jobCandidate['nom'] ?? ''));
        $prenom = trim((string) ($this->request->getPost('prenom') ?? $jobCandidate['prenom'] ?? ''));
        $email = trim((string) ($this->request->getPost('email') ?? $jobCandidate['email'] ?? ''));
        $dateEmbauche = (string) ($this->request->getPost('date_embauche') ?? date('Y-m-d'));
        $departementId = (int) ($this->request->getPost('departement_id') ?? 0);

        if ($nom === '' || $prenom === '' || $email === '' || $departementId <= 0 || $dateEmbauche === '') {
            return redirect()->back()->withInput()->with('error', 'Tous les champs du dossier salarié sont obligatoires.');
        }

        $lastEmployee = $db->table('employe')->select('matricule')->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
        $nextNumber = 1;

        if (! empty($lastEmployee['matricule'])) {
            preg_match('/^(EMP-)(\d+)$/', (string) $lastEmployee['matricule'], $matches);
            if (! empty($matches[2])) {
                $nextNumber = (int) $matches[2] + 1;
            }
        }

        $matricule = 'EMP-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

        $employeeModel = new EmployeModel();
        $employeeModel->insert([
            'matricule' => $matricule,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'departement_id' => $departementId,
            'role_id' => null,
            'candidat_id' => $candidateId,
            'date_embauche' => $dateEmbauche,
            'actif' => true,
        ]);

        return redirect()->to('bloc3/employees')->with('success', 'Le dossier salarié a bien été créé.');
    }
}
