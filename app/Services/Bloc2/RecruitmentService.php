<?php

namespace App\Services\Bloc2;

use App\Models\CandidatModel;
use App\Models\CandidatureModel;
use App\Models\EntretienModel;
use App\Models\EvaluationCandidatModel;
use App\Models\HistoriqueRecrutementModel;
use App\Models\OffreEmploiModel;
use CodeIgniter\Database\BaseConnection;
use Throwable;

/** Règles métier et transactions du recrutement. */
class RecruitmentService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null) { $this->db = $db ?? db_connect(); }

    public function dashboard(): array
    {
        return [
            'offers' => $this->count('offre_emploi'), 'open_offers' => $this->count('offre_emploi', ['statut' => 'PUBLIEE']),
            'candidates' => $this->count('candidat'), 'applications' => $this->count('candidature'),
            'interviews' => $this->count('entretien', ['statut' => 'PLANIFIE']), 'retained' => $this->count('candidature', ['statut' => 'RETENUE']),
        ];
    }

    public function offers(array $filters = []): array
    {
        $b = $this->db->table('offre_emploi o')->select('o.*, d.intitule AS demande_intitule, p.intitule AS poste_intitule, COUNT(c.id) AS candidature_count')
            ->join('demande d', 'd.id = o.demande_recrutement_id', 'left')->join('poste p', 'p.id = o.poste_id', 'left')
            ->join('candidature c', 'c.offre_emploi_id = o.id', 'left')
            ->groupBy('o.id, d.intitule, p.intitule')->orderBy('o.id', 'DESC');
        if (! empty($filters['statut'])) { $b->where('o.statut', $filters['statut']); }
        if ($q = trim((string) ($filters['q'] ?? ''))) { $b->groupStart()->like('LOWER(o.titre)', mb_strtolower($q))->orLike('LOWER(d.intitule)', mb_strtolower($q))->orLike('LOWER(p.intitule)', mb_strtolower($q))->groupEnd(); }
        return $b->get()->getResultArray();
    }

    public function allOffers(): array { return $this->db->table('offre_emploi')->orderBy('titre')->get()->getResultArray(); }
    public function openOffers(): array { return $this->db->table('offre_emploi')->where('statut', 'PUBLIEE')->groupStart()->where('date_cloture IS NULL', null, false)->orWhere('date_cloture >=', date('Y-m-d'))->groupEnd()->orderBy('titre')->get()->getResultArray(); }
    public function offer(int $id): ?array { return $this->row('offre_emploi', $id); }

    public function approvedRequests(?int $include = null): array
    {
        $b = $this->db->table('demande d')->select('d.*, s.code AS status_code')->join('status s', 's.id = d.status_id')
            ->where('s.code', 'DIRECTION_validee')->orderBy('d.id', 'DESC');
        $rows = $b->get()->getResultArray();
        return array_values(array_filter($rows, fn (array $r): bool => $this->isRequestApproved((int) $r['id'])));
    }

    /** Postes issus d'une demande approuvée et non pris par une autre offre active. */
    public function availablePositions(?int $includeOfferId = null): array
    {
        $includedPositionId = $includeOfferId ? (int) ($this->offer($includeOfferId)['poste_id'] ?? 0) : 0;
        $rows = $this->db->table('poste p')
            ->select('p.id, p.intitule, p.demande_id, d.intitule AS demande_intitule')
            ->join('demande d', 'd.id = p.demande_id')
            ->orderBy('d.id', 'DESC')->orderBy('p.intitule')->get()->getResultArray();

        return array_values(array_filter($rows, function (array $position) use ($includeOfferId, $includedPositionId): bool {
            return $this->isRequestApproved((int) $position['demande_id'])
                && ((int) $position['id'] === $includedPositionId || $this->positionAvailable((int) $position['id'], $includeOfferId));
        }));
    }

    public function saveOffer(array $input): array
    {
        $id = (int) ($input['id'] ?? 0); $requestId = (int) ($input['demande_recrutement_id'] ?? 0);
        $positionId = (int) ($input['poste_id'] ?? 0);
        $title = trim((string) ($input['titre'] ?? '')); $description = trim((string) ($input['description'] ?? ''));
        $closing = trim((string) ($input['date_cloture'] ?? '')) ?: null;
        $position = $this->row('poste', $positionId);
        if (! $position || (int) $position['demande_id'] !== $requestId) { return $this->fail('Sélectionnez un poste précis appartenant à cette demande.'); }
        if (! $this->positionAvailable($positionId, $id ?: null)) { return $this->fail('Ce poste est déjà occupé ou associé à une autre offre active.'); }
        if (! $this->isRequestApproved($requestId)) { return $this->fail('La demande doit être approuvée par la Direction et posséder sa validation Direction.'); }
        if ($title === '' || mb_strlen($title) > 150) { return $this->fail('Le titre est obligatoire et limité à 150 caractères.'); }
        if ($closing && ! $this->validDate($closing)) { return $this->fail('La date de clôture est invalide.'); }
        $model = new OffreEmploiModel($this->db); $old = $id ? $this->offer($id) : null;
        if ($id && ($old === null || $old['statut'] !== 'BROUILLON')) { return $this->fail('Seule une offre en brouillon peut être modifiée.'); }
        return $this->transaction(function () use ($model, $id, $old, $requestId, $positionId, $title, $description, $closing) {
            $data = ['demande_recrutement_id' => $requestId, 'poste_id' => $positionId, 'titre' => $title, 'description' => $description ?: null,
                'date_publication' => null, 'date_cloture' => $closing, 'statut' => 'BROUILLON'];
            $saved = $id ? $model->update($id, $data) : ($id = (int) $model->insert($data));
            if ($saved === false || ! $id) { throw new \RuntimeException(implode(' ', $model->errors()) ?: 'Enregistrement impossible.'); }
            $this->log('OFFRE', $id, $old ? 'OFFRE_MODIFIEE' : 'OFFRE_CREEE', $old, $data);
            return ['ok' => true, 'message' => $old ? 'Offre mise à jour.' : 'Offre créée au statut brouillon.'];
        });
    }

    public function publishOffer(int $id): array
    {
        $offer = $this->offer($id);
        if (! $offer || $offer['statut'] !== 'BROUILLON') { return $this->fail('Seule une offre en brouillon peut être publiée.'); }
        if (! $this->isRequestApproved((int) $offer['demande_recrutement_id'])) { return $this->fail('La demande n’est pas approuvée par la Direction.'); }
        if ($offer['date_cloture'] && $offer['date_cloture'] < date('Y-m-d')) { return $this->fail('La date de clôture est déjà dépassée.'); }
        if (! $this->positionMatchesOffer($offer) || ! $this->positionAvailable((int) $offer['poste_id'], $id)) { return $this->fail('Le poste de cette offre est invalide, occupé ou déjà recruté.'); }
        return $this->statusTransaction(new OffreEmploiModel($this->db), 'OFFRE', $offer, 'PUBLIEE', 'OFFRE_PUBLIEE', ['date_publication' => date('Y-m-d')]);
    }

    public function closeOffer(int $id): array
    {
        $offer = $this->offer($id);
        if (! $offer || $offer['statut'] !== 'PUBLIEE') { return $this->fail('Seule une offre publiée peut être clôturée.'); }
        return $this->statusTransaction(new OffreEmploiModel($this->db), 'OFFRE', $offer, 'CLOTUREE', 'OFFRE_CLOTUREE', ['date_cloture' => $offer['date_cloture'] ?: date('Y-m-d')]);
    }

    public function candidates(string $q = ''): array
    {
        $b = $this->db->table('candidat')->orderBy('nom')->orderBy('prenom');
        if ($q = trim($q)) { $b->groupStart()->like('LOWER(nom)', mb_strtolower($q))->orLike('LOWER(prenom)', mb_strtolower($q))->orLike('LOWER(email)', mb_strtolower($q))->groupEnd(); }
        return $b->get()->getResultArray();
    }

    public function candidate(int $id): ?array { return $this->row('candidat', $id); }

    public function saveCandidate(array $input): array
    {
        $id = (int) ($input['id'] ?? 0); $email = mb_strtolower(trim((string) ($input['email'] ?? '')));
        $data = ['nom' => trim((string) ($input['nom'] ?? '')), 'prenom' => trim((string) ($input['prenom'] ?? '')), 'email' => $email,
            'telephone' => trim((string) ($input['telephone'] ?? '')) ?: null, 'cv' => trim((string) ($input['cv'] ?? '')) ?: null];
        if ($data['nom'] === '' || $data['prenom'] === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) { return $this->fail('Nom, prénom et adresse e-mail valide sont obligatoires.'); }
        $duplicate = $this->db->table('candidat')->where('LOWER(email)', $email)->where('id !=', $id ?: 0)->get()->getFirstRow('array');
        if ($duplicate) { return $this->fail('Un candidat possède déjà cette adresse e-mail.'); }
        $model = new CandidatModel($this->db); $old = $id ? $this->candidate($id) : null;
        if ($id && ! $old) { return $this->fail('Candidat introuvable.'); }
        return $this->transaction(function () use ($model, &$id, $old, $data) {
            $saved = $id ? $model->update($id, $data) : ($id = (int) $model->insert($data));
            if ($saved === false || ! $id) { throw new \RuntimeException(implode(' ', $model->errors()) ?: 'Enregistrement impossible.'); }
            $this->log('CANDIDAT', $id, $old ? 'CANDIDAT_MODIFIE' : 'CANDIDAT_CREE', $old, $data);
            return ['ok' => true, 'message' => 'Candidat enregistré.', 'redirect' => 'bloc2/candidates/' . $id];
        });
    }

    public function applications(array $filters = []): array
    {
        $b = $this->applicationQuery()->orderBy('ca.id', 'DESC');
        foreach (['candidat_id' => 'ca.candidat_id', 'offre_emploi_id' => 'ca.offre_emploi_id', 'statut' => 'ca.statut'] as $key => $field) {
            if (! empty($filters[$key])) { $b->where($field, $filters[$key]); }
        }
        return $b->get()->getResultArray();
    }

    public function saveApplication(array $input): array
    {
        $candidateId = (int) ($input['candidat_id'] ?? 0); $offerId = (int) ($input['offre_emploi_id'] ?? 0);
        $candidate = $this->candidate($candidateId); $offer = $this->offer($offerId);
        if (! $candidate || ! $offer) { return $this->fail('Le candidat ou l’offre est introuvable.'); }
        if ($offer['statut'] !== 'PUBLIEE' || ($offer['date_cloture'] && $offer['date_cloture'] < date('Y-m-d'))) { return $this->fail('Cette offre n’accepte plus de candidatures.'); }
        if ($this->db->table('candidature')->where(['candidat_id' => $candidateId, 'offre_emploi_id' => $offerId])->countAllResults()) { return $this->fail('Ce candidat a déjà postulé à cette offre.'); }
        if (! $this->positionMatchesOffer($offer) || ! $this->positionAvailable((int) $offer['poste_id'], $offerId)) { return $this->fail('Le poste associé à cette offre n’est plus disponible.'); }
        $model = new CandidatureModel($this->db);
        return $this->transaction(function () use ($model, $candidateId, $offerId, $input) {
            $data = ['candidat_id' => $candidateId, 'offre_emploi_id' => $offerId,
                'lettre_motivation' => trim((string) ($input['lettre_motivation'] ?? '')) ?: null, 'statut' => 'EN_ATTENTE'];
            $id = $model->insert($data);
            if ($id === false) { throw new \RuntimeException(implode(' ', $model->errors()) ?: 'Candidature impossible.'); }
            $this->log('CANDIDATURE', (int) $id, 'CANDIDATURE_RECUE', null, $data);
            return ['ok' => true, 'message' => 'Candidature enregistrée.'];
        });
    }

    public function applicationsForPreselection(): array { return $this->applicationQuery()->whereIn('ca.statut', ['EN_ATTENTE', 'PRESELECTIONNEE'])->orderBy('ca.id', 'DESC')->get()->getResultArray(); }

    /** Une candidature déjà en entretien reste planifiable pour les étapes suivantes. */
    public function interviewableApplications(): array
    {
        return $this->applicationQuery()->whereIn('ca.statut', ['PRESELECTIONNEE', 'EN_ENTRETIEN'])->orderBy('ca.id', 'DESC')->get()->getResultArray();
    }

    public function changeApplicationStatus(int $id, array $input): array
    {
        $app = $this->row('candidature', $id); $next = (string) ($input['statut'] ?? ''); $reason = trim((string) ($input['motif'] ?? ''));
        $allowed = ['EN_ATTENTE' => ['PRESELECTIONNEE', 'REJETEE'], 'PRESELECTIONNEE' => ['REJETEE']];
        if (! $app || ! in_array($next, $allowed[$app['statut']] ?? [], true)) { return $this->fail('Transition de candidature interdite.'); }
        if ($next === 'REJETEE' && $reason === '') { return $this->fail('Le motif du rejet est obligatoire.'); }
        return $this->statusTransaction(new CandidatureModel($this->db), 'CANDIDATURE', $app, $next,
            $next === 'REJETEE' ? 'CANDIDATURE_REJETEE' : 'CANDIDATURE_PRESELECTIONNEE', [], $reason);
    }

    public function interviews(array $filters = []): array
    {
        $b = $this->interviewQuery()->orderBy('e.date_entretien', 'DESC');
        if (! empty($filters['statut'])) { $b->where('e.statut', $filters['statut']); }
        return $b->get()->getResultArray();
    }

    public function interview(int $id): ?array { return $this->interviewQuery()->where('e.id', $id)->get()->getFirstRow('array'); }

    public function saveInterview(array $input): array
    {
        $id = (int) ($input['id'] ?? 0); $applicationId = (int) ($input['candidature_id'] ?? 0); $app = $this->row('candidature', $applicationId);
        $date = str_replace('T', ' ', trim((string) ($input['date_entretien'] ?? ''))); $type = strtoupper(trim((string) ($input['type_entretien'] ?? '')));
        $location = trim((string) ($input['lieu'] ?? ''));
        $participantIds = array_values(array_unique(array_filter(array_map('intval', (array) ($input['participant_ids'] ?? [])))));
        if (! $app || (! $id && ! in_array($app['statut'], ['PRESELECTIONNEE', 'EN_ENTRETIEN'], true))) {
            return $this->fail('La candidature doit être présélectionnée ou déjà en cours d’entretien.');
        }
        if (! in_array($type, ['RH', 'TECHNIQUE', 'FINAL', 'TEST_TECHNIQUE'], true) || ! strtotime($date) || $location === '') { return $this->fail('Date, type et lieu de l’entretien sont obligatoires.'); }
        if ($participantIds === [] || $this->db->table('employe')->whereIn('id', $participantIds)->where('actif', true)->countAllResults() !== count($participantIds)) {
            return $this->fail('Sélectionnez au moins un participant actif.');
        }
        $old = $id ? $this->row('entretien', $id) : null;
        if ($id && (! $old || $old['statut'] !== 'PLANIFIE' || (int) $old['candidature_id'] !== $applicationId)) { return $this->fail('Seul un entretien planifié peut être modifié sans changer de candidature.'); }
        $otherPlanned = $this->db->table('entretien')->where('candidature_id', $applicationId)->where('statut', 'PLANIFIE');
        if ($id) { $otherPlanned->where('id !=', $id); }
        if ($otherPlanned->countAllResults() > 0) { return $this->fail('Un entretien est déjà planifié pour cette candidature. Terminez-le ou annulez-le avant d’en créer un autre.'); }
        return $this->transaction(function () use (&$id, $old, $app, $applicationId, $date, $type, $location, $participantIds) {
            $model = new EntretienModel($this->db); $data = ['candidature_id' => $applicationId, 'date_entretien' => $date, 'type_entretien' => $type,
                'lieu' => $location, 'statut' => 'PLANIFIE', 'compte_rendu' => $old['compte_rendu'] ?? null];
            $saved = $id ? $model->update($id, $data) : ($id = (int) $model->insert($data));
            if ($saved === false || ! $id) { throw new \RuntimeException(implode(' ', $model->errors()) ?: 'Planification impossible.'); }
            if (! $old && $app['statut'] === 'PRESELECTIONNEE' && ! (new CandidatureModel($this->db))->update($applicationId, ['statut' => 'EN_ENTRETIEN'])) {
                throw new \RuntimeException('La candidature n’a pas pu passer en entretien.');
            }
            $this->db->table('entretien_participant')->where('entretien_id', $id)->delete();
            foreach ($participantIds as $participantId) {
                if (! $this->db->table('entretien_participant')->insert(['entretien_id' => $id, 'employe_id' => $participantId, 'role_participant' => 'EVALUATEUR'])) {
                    throw new \RuntimeException('Les participants n’ont pas pu être affectés.');
                }
            }
            $this->log('ENTRETIEN', $id, $old ? 'ENTRETIEN_MODIFIE' : 'ENTRETIEN_PLANIFIE', $old, $data + ['participant_ids' => $participantIds]);
            return ['ok' => true, 'message' => $old ? 'Entretien modifié.' : 'Entretien planifié.'];
        });
    }

    public function changeInterviewStatus(int $id, array $input): array
    {
        $next = strtoupper((string) ($input['statut'] ?? ''));
        $interview = $this->row('entretien', $id); $allowed = ['PLANIFIE' => ['REALISE', 'ANNULE']];
        if (! $interview || ! in_array($next, $allowed[$interview['statut']] ?? [], true)) { return $this->fail('Transition d’entretien interdite.'); }
        $report = trim((string) ($input['compte_rendu'] ?? ''));
        if ($next === 'REALISE' && $report === '') { return $this->fail('Le compte rendu est obligatoire pour marquer l’entretien réalisé.'); }
        return $this->statusTransaction(new EntretienModel($this->db), 'ENTRETIEN', $interview, $next,
            $next === 'REALISE' ? 'ENTRETIEN_REALISE' : 'ENTRETIEN_ANNULE', $report ? ['compte_rendu' => $report] : []);
    }

    public function completedInterviews(): array { return $this->interviewQuery()->where('e.statut', 'REALISE')->orderBy('e.date_entretien', 'DESC')->get()->getResultArray(); }
    public function evaluations(int $interviewId): array { return $this->db->table('evaluation_candidat ev')->select('ev.*, emp.nom AS evaluateur_nom, emp.prenom AS evaluateur_prenom')->join('employe emp', 'emp.id = ev.evaluateur_id', 'left')->where('ev.entretien_id', $interviewId)->orderBy('ev.id', 'DESC')->get()->getResultArray(); }
    public function employees(): array { return $this->db->table('employe')->select('id, nom, prenom, matricule')->where('actif', true)->orderBy('nom')->get()->getResultArray(); }

    public function interviewParticipants(int $interviewId): array
    {
        return $this->db->table('entretien_participant ep')->select('emp.id, emp.nom, emp.prenom, emp.matricule, ep.role_participant')
            ->join('employe emp', 'emp.id = ep.employe_id')->where('ep.entretien_id', $interviewId)->where('emp.actif', true)
            ->orderBy('emp.nom')->get()->getResultArray();
    }

    public function saveEvaluation(array $input): array
    {
        $interviewId = (int) ($input['entretien_id'] ?? 0); $employeeId = (int) ($input['evaluateur_id'] ?? 0); $interview = $this->row('entretien', $interviewId);
        $note = filter_var($input['note'] ?? null, FILTER_VALIDATE_FLOAT); $decision = strtoupper((string) ($input['decision'] ?? '')); $comment = trim((string) ($input['commentaire'] ?? ''));
        if (! $interview || $interview['statut'] !== 'REALISE') { return $this->fail('Seul un entretien réalisé peut être évalué.'); }
        if (! $this->db->table('entretien_participant')->where(['entretien_id' => $interviewId, 'employe_id' => $employeeId])->countAllResults()) {
            return $this->fail('Cet évaluateur n’est pas affecté à cet entretien.');
        }
        if (! $this->row('employe', $employeeId) || $note === false || $note < 0 || $note > 20 || ! in_array($decision, ['FAVORABLE', 'DEFAVORABLE', 'A_REVOIR'], true) || $comment === '') { return $this->fail('Évaluateur, note de 0 à 20, avis et commentaire sont obligatoires.'); }
        if ($this->db->table('evaluation_candidat')->where(['entretien_id' => $interviewId, 'evaluateur_id' => $employeeId])->countAllResults()) { return $this->fail('Cet évaluateur a déjà évalué cet entretien.'); }
        return $this->transaction(function () use ($interviewId, $employeeId, $note, $decision, $comment) {
            $model = new EvaluationCandidatModel($this->db); $data = ['entretien_id' => $interviewId, 'evaluateur_id' => $employeeId, 'note' => $note, 'commentaire' => $comment, 'decision' => $decision];
            $id = $model->insert($data); if ($id === false) { throw new \RuntimeException(implode(' ', $model->errors()) ?: 'Évaluation impossible.'); }
            $this->log('EVALUATION', (int) $id, 'EVALUATION_SAISIE', null, $data);
            return ['ok' => true, 'message' => 'Évaluation enregistrée.'];
        });
    }

    public function decisionFiles(): array
    {
        $apps = $this->applicationQuery()->where('ca.statut', 'EN_ENTRETIEN')->orderBy('ca.id', 'DESC')->get()->getResultArray();
        foreach ($apps as &$app) { $app['evaluations'] = $this->db->table('evaluation_candidat ev')->select('ev.*')->join('entretien e', 'e.id = ev.entretien_id')->where('e.candidature_id', $app['id'])->get()->getResultArray(); }
        return $apps;
    }

    public function finalDecision(int $id, array $input): array
    {
        $app = $this->row('candidature', $id); $next = strtoupper((string) ($input['decision'] ?? '')); $reason = trim((string) ($input['motif'] ?? ''));
        if (! $app || $app['statut'] !== 'EN_ENTRETIEN' || ! in_array($next, ['RETENUE', 'REJETEE'], true)) { return $this->fail('Décision finale interdite pour ce dossier.'); }
        $plannedInterviewCount = $this->db->table('entretien')->where('candidature_id', $id)->where('statut', 'PLANIFIE')->countAllResults();
        if ($plannedInterviewCount > 0) { return $this->fail('La décision finale est interdite tant qu’un entretien reste planifié.'); }
        $evaluationCount = $this->db->table('evaluation_candidat ev')->join('entretien e', 'e.id = ev.entretien_id')->where('e.candidature_id', $id)->countAllResults();
        if (! $evaluationCount) { return $this->fail('Au moins une évaluation est requise avant la décision finale.'); }
        if ($next === 'REJETEE' && $reason === '') { return $this->fail('Le motif du rejet est obligatoire.'); }
        $offer = $this->offer((int) $app['offre_emploi_id']);
        if ($next === 'RETENUE' && (! $offer || ! $this->positionMatchesOffer($offer) || ! $this->positionAvailableForSelection((int) $offer['poste_id'], $id))) {
            return $this->fail('Ce poste possède déjà un candidat retenu ou un titulaire actif.');
        }
        if ($next === 'RETENUE') {
            return $this->transaction(function () use ($app, $offer, $id, $reason) {
                $positionId = (int) $offer['poste_id'];
                if ($this->db->DBDriver === 'Postgre') {
                    $this->db->query('SELECT pg_advisory_xact_lock(?)', [$positionId]);
                }
                if (! $this->positionAvailableForSelection($positionId, $id)) {
                    throw new \RuntimeException('Ce poste possède déjà un candidat retenu ou un titulaire actif.');
                }
                if (! (new CandidatureModel($this->db))->update($id, ['statut' => 'RETENUE'])) {
                    throw new \RuntimeException('La candidature n’a pas pu être retenue.');
                }
                $this->log('CANDIDATURE', $id, 'DECISION_FINALE', ['statut' => $app['statut']], ['statut' => 'RETENUE'] + ($reason ? ['motif' => $reason] : []));
                if ($offer['statut'] === 'PUBLIEE') {
                    $offerUpdate = ['statut' => 'CLOTUREE', 'date_cloture' => $offer['date_cloture'] ?: date('Y-m-d')];
                    if (! $this->db->table('offre_emploi')->where('id', $offer['id'])->update($offerUpdate)) {
                        throw new \RuntimeException('L’offre n’a pas pu être clôturée.');
                    }
                    $this->log('OFFRE', (int) $offer['id'], 'OFFRE_CLOTUREE_APRES_SELECTION', ['statut' => 'PUBLIEE'], $offerUpdate);
                }
                return ['ok' => true, 'message' => 'Candidature retenue et poste réservé.'];
            });
        }
        return $this->statusTransaction(new CandidatureModel($this->db), 'CANDIDATURE', $app, $next, 'DECISION_FINALE', [], $reason);
    }

    public function history(array $filters = []): array
    {
        $b = $this->db->table('historique_recrutement h')->select('h.*, e.nom AS auteur_nom, e.prenom AS auteur_prenom')->join('employe e', 'e.id = h.utilisateur_id', 'left')->orderBy('h.id', 'DESC');
        if (! empty($filters['action'])) { $b->where('h.action', $filters['action']); }
        if (! empty($filters['entite'])) { $b->where('h.entite', $filters['entite']); }
        if (! empty($filters['utilisateur_id'])) { $b->where('h.utilisateur_id', $filters['utilisateur_id']); }
        if (! empty($filters['date_debut'])) { $b->where('h.date_action >=', $filters['date_debut'] . ' 00:00:00'); }
        if (! empty($filters['date_fin'])) { $b->where('h.date_action <=', $filters['date_fin'] . ' 23:59:59'); }
        return $b->get()->getResultArray();
    }

    private function applicationQuery() { return $this->db->table('candidature ca')->select('ca.*, c.nom, c.prenom, c.email, o.titre AS offre_titre, o.poste_id, p.intitule AS poste_intitule')->join('candidat c', 'c.id = ca.candidat_id')->join('offre_emploi o', 'o.id = ca.offre_emploi_id')->join('poste p', 'p.id = o.poste_id', 'left'); }
    private function interviewQuery() { return $this->db->table('entretien e')->select('e.*, ca.statut AS candidature_statut, c.nom, c.prenom, o.titre AS offre_titre, o.poste_id, p.intitule AS poste_intitule')->join('candidature ca', 'ca.id = e.candidature_id')->join('candidat c', 'c.id = ca.candidat_id')->join('offre_emploi o', 'o.id = ca.offre_emploi_id')->join('poste p', 'p.id = o.poste_id', 'left'); }
    private function row(string $table, int $id): ?array { return $id > 0 ? $this->db->table($table)->where('id', $id)->get()->getFirstRow('array') : null; }
    private function count(string $table, array $where = []): int { return $this->db->table($table)->where($where)->countAllResults(); }
    private function validDate(string $date): bool { $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $date); return $d && $d->format('Y-m-d') === $date; }

    private function isRequestApproved(int $id): bool
    {
        if ($id < 1) { return false; }
        $current = $this->db->table('demande d')->join('status s', 's.id = d.status_id')->where('d.id', $id)->where('s.code', 'DIRECTION_validee')->countAllResults();
        $validation = $this->db->table('validation v')->join('status s', 's.id = v.status_id')->where('v.demande_id', $id)->where('s.code', 'DIRECTION_validee')->countAllResults();
        return $current > 0 && $validation > 0;
    }

    private function positionMatchesOffer(array $offer): bool
    {
        $position = $this->row('poste', (int) ($offer['poste_id'] ?? 0));
        return $position !== null && (int) $position['demande_id'] === (int) $offer['demande_recrutement_id'];
    }

    private function positionAvailable(int $positionId, ?int $includeOfferId = null): bool
    {
        if ($positionId < 1 || $this->row('poste', $positionId) === null) { return false; }
        if ($this->db->tableExists('affectation')
            && $this->db->table('affectation')->where('poste_id', $positionId)->where('date_fin IS NULL', null, false)->countAllResults() > 0) {
            return false;
        }
        $offers = $this->db->table('offre_emploi')->where('poste_id', $positionId)->whereIn('statut', ['BROUILLON', 'PUBLIEE']);
        if ($includeOfferId) { $offers->where('id !=', $includeOfferId); }
        if ($offers->countAllResults() > 0) { return false; }
        return $this->db->table('candidature ca')->join('offre_emploi o', 'o.id = ca.offre_emploi_id')
            ->where('o.poste_id', $positionId)->where('ca.statut', 'RETENUE')->countAllResults() === 0;
    }

    private function positionAvailableForSelection(int $positionId, int $applicationId): bool
    {
        if ($this->db->tableExists('affectation')
            && $this->db->table('affectation')->where('poste_id', $positionId)->where('date_fin IS NULL', null, false)->countAllResults() > 0) {
            return false;
        }
        return $this->db->table('candidature ca')->join('offre_emploi o', 'o.id = ca.offre_emploi_id')
            ->where('o.poste_id', $positionId)->where('ca.statut', 'RETENUE')->where('ca.id !=', $applicationId)->countAllResults() === 0;
    }

    private function statusTransaction($model, string $entity, array $old, string $next, string $action, array $extra = [], string $comment = ''): array
    {
        return $this->transaction(function () use ($model, $entity, $old, $next, $action, $extra, $comment) {
            $data = ['statut' => $next] + $extra;
            if (! $model->update((int) $old['id'], $data)) { throw new \RuntimeException(implode(' ', $model->errors()) ?: 'Mise à jour impossible.'); }
            $this->log($entity, (int) $old['id'], $action, ['statut' => $old['statut']], $data + ($comment ? ['motif' => $comment] : []));
            return ['ok' => true, 'message' => 'Opération enregistrée.'];
        });
    }

    private function log(string $entity, int $id, string $action, ?array $old, ?array $new): void
    {
        $model = new HistoriqueRecrutementModel($this->db);
        if ($model->insert(['entite' => $entity, 'entite_id' => $id, 'action' => $action, 'utilisateur_id' => null,
            'ancienne_valeur' => $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
            'nouvelle_valeur' => $new ? json_encode($new, JSON_UNESCAPED_UNICODE) : null]) === false) {
            throw new \RuntimeException('L’historique n’a pas pu être enregistré.');
        }
    }

    private function transaction(callable $operation): array
    {
        $this->db->transBegin();
        try {
            $result = $operation();
            if ($this->db->transStatus() === false) { throw new \RuntimeException('La transaction a échoué.'); }
            $this->db->transCommit(); return $result;
        } catch (Throwable $e) {
            $this->db->transRollback(); log_message('error', 'Bloc 2: {message}', ['message' => $e->getMessage()]);
            return $this->fail($e->getMessage());
        }
    }

    private function fail(string $message, array $errors = []): array { return ['ok' => false, 'message' => $message, 'errors' => $errors]; }
}
