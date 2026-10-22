<?php

namespace App\Controllers\Bloc3;

use App\Controllers\BaseController;
use App\Models\PropositionEmbaucheModel;

class ResponsesController extends BaseController
{
    public function index()
    {
        $propositionModel = new PropositionEmbaucheModel();

        $rows = $propositionModel
            ->select([
                'proposition_embauche.id AS proposition_id',
                "CONCAT(candidat.prenom, ' ', candidat.nom) AS candidat",
                'offre_emploi.titre AS poste',
                'type_contrat.code AS type_contrat',
                'proposition_embauche.date_reponse',
                'proposition_embauche.reponse AS decision',
                'proposition_embauche.date_debut',
            ])
            ->join('candidature', 'candidature.id = proposition_embauche.candidature_id', 'left')
            ->join('candidat', 'candidat.id = candidature.candidat_id', 'left')
            ->join('offre_emploi', 'offre_emploi.id = candidature.offre_emploi_id', 'left')
            ->join('type_contrat', 'type_contrat.id = proposition_embauche.type_contrat_id', 'left')
            ->orderBy('proposition_embauche.id', 'DESC')
            ->asArray()
            ->findAll();

        $pendingProposals = $propositionModel
            ->select([
                'proposition_embauche.id AS proposition_id',
                "CONCAT(candidat.prenom, ' ', candidat.nom) AS candidat",
            ])
            ->join('candidature', 'candidature.id = proposition_embauche.candidature_id', 'left')
            ->join('candidat', 'candidat.id = candidature.candidat_id', 'left')
            ->where('proposition_embauche.reponse', 'EN_ATTENTE')
            ->orderBy('proposition_embauche.id', 'DESC')
            ->asArray()
            ->findAll();

        $candidateOptions = ['' => 'Choisir un candidat'];
        foreach ($pendingProposals as $proposal) {
            $candidateId = (string) ($proposal['proposition_id'] ?? '');
            $candidateName = trim((string) ($proposal['candidat'] ?? ''));
            if ($candidateId !== '' && $candidateName !== '') {
                $candidateOptions[$candidateId] = $candidateName;
            }
        }

        $responses = array_map(static function (array $row): array {
            $decision = strtoupper((string) ($row['decision'] ?? 'EN_ATTENTE'));
            $statusMap = [
                'EN_ATTENTE' => 'En attente de réponse',
                'ACCEPTEE' => 'Acceptée par le candidat',
                'REFUSEE' => 'Refusée par le candidat',
            ];

            return [
                'id' => (int) ($row['proposition_id'] ?? 0),
                'candidat' => $row['candidat'] ?? '—',
                'poste' => $row['poste'] ?? '—',
                'type_contrat' => $row['type_contrat'] ?? '—',
                'date_reponse' => $row['date_reponse'] ?? null,
                'decision' => $decision === 'ACCEPTEE' ? 'Acceptée' : ($decision === 'REFUSEE' ? 'Refusée' : 'En attente'),
                'statut' => $statusMap[$decision] ?? 'En attente de réponse',
            ];
        }, $rows);

        return view('bloc3/responses', [
            'responses' => $responses,
            'candidateOptions' => $candidateOptions,
        ]);
    }

    public function save()
    {
        $propositionId = (int) ($this->request->getPost('candidate') ?? 0);
        $decision = (string) ($this->request->getPost('decision') ?? '');
        $dateResponse = (string) ($this->request->getPost('date_response') ?? '');

        if ($propositionId <= 0 || ! in_array($decision, ['Acceptée', 'Refusée'], true)) {
            return redirect()->back()->withInput()->with('error', 'Veuillez choisir un candidat puis une décision valide.');
        }

        $reponse = $decision === 'Acceptée' ? 'ACCEPTEE' : 'REFUSEE';
        $dateValue = $dateResponse !== '' ? $dateResponse : date('Y-m-d');

        $propositionModel = new PropositionEmbaucheModel();
        $propositionModel->update($propositionId, [
            'reponse' => $reponse,
            'date_reponse' => $dateValue,
        ]);

        return redirect()->to('bloc3/responses')->with('success', 'Réponse du candidat enregistrée.');
    }
}
