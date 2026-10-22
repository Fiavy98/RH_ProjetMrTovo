<?php

namespace App\Controllers\Bloc3;

use App\Controllers\BaseController;
use App\Models\PropositionEmbaucheModel;

class HireValidationsController extends BaseController
{
    public function index()
    {
        $propositionModel = new PropositionEmbaucheModel();

        $rows = $propositionModel
            ->select([
                'proposition_embauche.id AS proposition_id',
                "CONCAT(candidat.prenom, ' ', candidat.nom) AS candidat",
                'offre_emploi.titre AS poste',
                'proposition_embauche.salaire',
                'type_contrat.code AS contrat',
                'proposition_embauche.date_debut',
                'proposition_embauche.reponse AS decision_rh',
                'proposition_embauche.decision_direction',
                'proposition_embauche.commentaire_direction',
            ])
            ->join('candidature', 'candidature.id = proposition_embauche.candidature_id', 'left')
            ->join('candidat', 'candidat.id = candidature.candidat_id', 'left')
            ->join('offre_emploi', 'offre_emploi.id = candidature.offre_emploi_id', 'left')
            ->join('type_contrat', 'type_contrat.id = proposition_embauche.type_contrat_id', 'left')
            ->where('proposition_embauche.reponse', 'ACCEPTEE')
            ->orderBy('proposition_embauche.id', 'DESC')
            ->asArray()
            ->findAll();

        $candidateOptions = ['' => 'Choisir un candidat'];
        $selectedProposal = null;
        $selectedProposalId = (int) ($this->request->getGet('proposal_id') ?? 0);

        foreach ($rows as $row) {
            $proposalId = (int) ($row['proposition_id'] ?? 0);
            $candidateName = trim((string) ($row['candidat'] ?? ''));
            if ($proposalId > 0 && $candidateName !== '') {
                $candidateOptions[(string) $proposalId] = $candidateName;
            }

            if ($selectedProposalId > 0 && $proposalId === $selectedProposalId) {
                $selectedProposal = $row;
            }
        }

        if ($selectedProposal === null && ! empty($rows)) {
            $selectedProposal = $rows[0];
            $selectedProposalId = (int) ($selectedProposal['proposition_id'] ?? 0);
        }

        $proposalDetails = [];
        foreach ($rows as $row) {
            $proposalId = (int) ($row['proposition_id'] ?? 0);
            if ($proposalId <= 0) {
                continue;
            }

            $proposalDetails[(string) $proposalId] = [
                'candidat' => $row['candidat'] ?? '—',
                'poste' => $row['poste'] ?? '—',
                'contrat' => $row['contrat'] ?? '—',
                'salaire' => number_format((float) ($row['salaire'] ?? 0), 0, ',', ' ') . ' Ar',
                'date_debut' => $row['date_debut'] ?? '—',
            ];
        }

        $validations = array_map(static function (array $row): array {
            return [
                'id' => (int) ($row['proposition_id'] ?? 0),
                'candidat' => $row['candidat'] ?? '—',
                'poste' => $row['poste'] ?? '—',
                'salaire' => number_format((float) ($row['salaire'] ?? 0), 0, ',', ' ') . ' Ar',
                'contrat' => $row['contrat'] ?? '—',
                'date_debut' => $row['date_debut'] ?? null,
                'decision_rh' => 'Acceptée par le candidat',
                'statut' => $row['decision_direction'] ?? 'À valider',
            ];
        }, $rows);

        return view('bloc3/hire_validations', [
            'validations' => $validations,
            'candidateOptions' => $candidateOptions,
            'selectedProposalId' => $selectedProposalId,
            'selectedProposal' => $selectedProposal,
            'proposalDetails' => $proposalDetails,
        ]);
    }

    public function save()
    {
        $proposalId = (int) ($this->request->getPost('candidate') ?? 0);
        $decision = (string) ($this->request->getPost('decision') ?? '');
        $comment = trim((string) ($this->request->getPost('comment') ?? ''));

        if ($proposalId <= 0 || ! in_array($decision, ['Accepter', 'Refuser'], true)) {
            return redirect()->back()->withInput()->with('error', 'Veuillez sélectionner une proposition et une décision valide.');
        }

        $decisionDirection = $decision === 'Accepter' ? 'VALIDEE' : 'REFUSEE';

        $propositionModel = new PropositionEmbaucheModel();
        $propositionModel->update($proposalId, [
            'decision_direction' => $decisionDirection,
            'date_decision_direction' => date('Y-m-d H:i:s'),
            'commentaire_direction' => $comment,
        ]);

        return redirect()->to('bloc3/hire-validations')->with('success', 'Décision de la direction enregistrée.');
    }
}
