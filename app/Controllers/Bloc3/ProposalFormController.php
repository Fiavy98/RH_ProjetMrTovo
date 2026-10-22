<?php

namespace App\Controllers\Bloc3;

use App\Controllers\BaseController;
use App\Entities\Candidature;
use App\Models\CandidatureModel;
use App\Models\DemandeModel;
use App\Models\OffreEmploiModel;
use App\Models\PropositionEmbaucheModel;
use App\Models\TypeContratModel;

class ProposalFormController extends BaseController
{
    public function index()
    {
        $candidatureModel = new CandidatureModel();
        $candidatureId = (int) ($this->request->getGet('candidature_id') ?? 0);

        $selected = $candidatureModel
            ->select([
                'candidature.id AS candidature_id',
                'candidature.candidat_id',
                'candidature.offre_emploi_id',
                'candidat.nom AS candidat_nom',
                'candidat.prenom AS candidat_prenom',
                'offre_emploi.titre AS poste',
                'offre_emploi.id AS offre_emploi_id',
                'demande.budget_min',
                'demande.budget_max',
                'proposition_embauche.salaire',
                'type_contrat.code AS type_contrat',
                'proposition_embauche.date_debut',
                'proposition_embauche.reponse',
            ])
            ->join('candidat', 'candidat.id = candidature.candidat_id', 'left')
            ->join('offre_emploi', 'offre_emploi.id = candidature.offre_emploi_id', 'left')
            ->join('demande', 'demande.id = offre_emploi.demande_recrutement_id', 'left')
            ->join('proposition_embauche', 'proposition_embauche.candidature_id = candidature.id', 'left')
            ->join('type_contrat', 'type_contrat.id = proposition_embauche.type_contrat_id', 'left')
            ->where('candidature.id', $candidatureId)
            ->asArray()
            ->first();

        if (empty($selected)) {
            $selected = $candidatureModel
                ->select([
                    'candidature.id AS candidature_id',
                    'candidature.candidat_id',
                    'candidature.offre_emploi_id',
                    'candidat.nom AS candidat_nom',
                    'candidat.prenom AS candidat_prenom',
                    'offre_emploi.titre AS poste',
                    'offre_emploi.id AS offre_emploi_id',
                    'demande.budget_min',
                    'demande.budget_max',
                    'proposition_embauche.salaire',
                    'type_contrat.code AS type_contrat',
                    'proposition_embauche.date_debut',
                    'proposition_embauche.reponse',
                ])
                ->join('candidat', 'candidat.id = candidature.candidat_id', 'left')
                ->join('offre_emploi', 'offre_emploi.id = candidature.offre_emploi_id', 'left')
                ->join('demande', 'demande.id = offre_emploi.demande_recrutement_id', 'left')
                ->join('proposition_embauche', 'proposition_embauche.candidature_id = candidature.id', 'left')
                ->join('type_contrat', 'type_contrat.id = proposition_embauche.type_contrat_id', 'left')
                ->where('candidature.statut', Candidature::STATUT_RETENUE)
                ->orderBy('candidature.id', 'DESC')
                ->asArray()
                ->first();
        }

        $selected = $selected ?? [
            'candidature_id' => 0,
            'candidat_id' => 0,
            'offre_emploi_id' => 0,
            'candidat_nom' => '',
            'candidat_prenom' => '',
            'poste' => '',
            'budget_min' => 0,
            'budget_max' => 0,
            'salaire' => 0,
            'type_contrat' => 'CE',
            'date_debut' => null,
            'reponse' => '',
        ];

        $budgetMin = (float) ($selected['budget_min'] ?? 0);
        $budgetMax = (float) ($selected['budget_max'] ?? 0);
        $salaryDefault = (float) ($selected['salaire'] ?? ($budgetMin > 0 ? $budgetMin : 0));

        $rows = $candidatureModel
            ->select([
                'candidature.id AS candidature_id',
                'candidat.nom',
                'candidat.prenom',
                'offre_emploi.titre AS poste',
                'proposition_embauche.salaire',
                'type_contrat.code AS type_contrat',
                'proposition_embauche.date_debut',
                'proposition_embauche.reponse',
                'proposition_embauche.date_proposition',
            ])
            ->join('candidat', 'candidat.id = candidature.candidat_id', 'left')
            ->join('offre_emploi', 'offre_emploi.id = candidature.offre_emploi_id', 'left')
            ->join('proposition_embauche', 'proposition_embauche.candidature_id = candidature.id', 'left')
            ->join('type_contrat', 'type_contrat.id = proposition_embauche.type_contrat_id', 'left')
            ->where('candidature.statut', Candidature::STATUT_RETENUE)
            ->orderBy('candidature.id', 'DESC')
            ->asArray()
            ->findAll();

        $candidatures = array_map(static function (array $row): array {
            $status = match (strtoupper((string) ($row['reponse'] ?? ''))) {
                'ACCEPTEE' => 'Acceptée par le candidat',
                'REFUSEE' => 'Refusée par le candidat',
                'EN_ATTENTE' => 'En attente de réponse',
                default => 'À préparer',
            };

            return [
                'id' => (int) ($row['candidature_id'] ?? 0),
                'nom' => $row['nom'] ?? '',
                'prenom' => $row['prenom'] ?? '',
                'poste' => $row['poste'] ?? '—',
                'type_contrat' => $row['type_contrat'] ?? '—',
                'salaire' => $row['salaire'] ?? 0,
                'date_debut' => $row['date_debut'] ?? null,
                'statut' => $status,
            ];
        }, $rows);

        return view('bloc3/proposal_form', [
            'candidatures' => $candidatures,
            'selected' => $selected,
            'budget_min' => $budgetMin,
            'budget_max' => $budgetMax,
            'salaire_default' => $salaryDefault,
        ]);
    }

    public function save()
    {
        $post = $this->request->getPost();
        $candidatureId = (int) ($post['candidature_id'] ?? $this->request->getGet('candidature_id') ?? 0);

        if ($candidatureId <= 0) {
            session()->setFlashdata('error', 'Aucune candidature sélectionnée.');
            return redirect()->to(site_url('bloc3/selected'));
        }

        $offreEmploiId = (int) ($post['offre_emploi_id'] ?? 0);
        $contractCode = strtoupper((string) ($post['contract_type'] ?? 'CE'));
        $salary = (float) str_replace([' ', ' ', ',', '\n', '\r'], '', trim((string) ($post['salary'] ?? '0')));
        $startDate = trim((string) ($post['start'] ?? date('Y-m-d')));

        $offreModel = new OffreEmploiModel();
        $offre = $offreModel->asArray()->find($offreEmploiId);

        if (empty($offre)) {
            $candidatureModel = new CandidatureModel();
            $candidature = $candidatureModel->asArray()->find($candidatureId);
            $offreEmploiId = (int) ($candidature['offre_emploi_id'] ?? 0);
            $offre = $offreModel->asArray()->find($offreEmploiId);
        }

        if (empty($offre)) {
            session()->setFlashdata('error', 'Offre d’emploi introuvable pour cette candidature.');
            return redirect()->to(site_url('bloc3/selected'));
        }

        $demandeModel = new DemandeModel();
        $demande = $demandeModel->asArray()->find((int) ($offre['demande_recrutement_id'] ?? 0));
        $budgetMin = $demande['budget_min'] ?? 0;
        $budgetMax = $demande['budget_max'] ?? 0;

        if (($budgetMin > 0 && $salary < (float) $budgetMin) || ($budgetMax > 0 && $salary > (float) $budgetMax)) {
            session()->setFlashdata('error', 'Le salaire proposé doit être compris entre ' . number_format((float) $budgetMin, 0, ',', ' ') . ' Ar et ' . number_format((float) $budgetMax, 0, ',', ' ') . ' Ar.');
            return redirect()->to(site_url('bloc3/proposal-form?candidature_id=' . $candidatureId));
        }

        $typeContratModel = new TypeContratModel();
        $typeContrat = $typeContratModel->asArray()->where('code', $contractCode)->first();

        if (empty($typeContrat)) {
            session()->setFlashdata('error', 'Type de contrat invalide.');
            return redirect()->to(site_url('bloc3/proposal-form?candidature_id=' . $candidatureId));
        }

        $propositionModel = new PropositionEmbaucheModel();
        $existing = $propositionModel->asArray()->where('candidature_id', $candidatureId)->first();

        $data = [
            'candidature_id' => $candidatureId,
            'salaire' => $salary,
            'type_contrat_id' => (int) $typeContrat['id'],
            'date_debut' => $startDate,
            'reponse' => 'EN_ATTENTE',
            'date_proposition' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $propositionModel->update((int) $existing['id'], $data);
        } else {
            $propositionModel->insert($data);
        }

        session()->setFlashdata('success', 'La proposition d’embauche a bien été enregistrée.');

        return redirect()->to(site_url('bloc3/proposal-form?candidature_id=' . $candidatureId));
    }
}
