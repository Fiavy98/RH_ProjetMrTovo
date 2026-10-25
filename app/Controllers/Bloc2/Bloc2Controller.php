<?php

namespace App\Controllers\Bloc2;

use App\Controllers\BaseController;
use App\Services\Bloc2\RecruitmentService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Bloc2Controller extends BaseController
{
    private RecruitmentService $recruitment;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->recruitment = new RecruitmentService();
    }

    public function index() { return redirect()->to(site_url('bloc2/dashboard')); }
    public function dashboard(): string { return $this->render('dashboard', 'Suivi du recrutement', ['stats' => $this->recruitment->dashboard()]); }
    public function offers(): string { return $this->render('offers', 'Offres d’emploi', ['offers' => $this->recruitment->offers($this->request->getGet())]); }

    public function offerForm(?int $id = null): string
    {
        $offer = $id ? $this->recruitment->offer($id) : null;
        if ($id && $offer === null) { throw PageNotFoundException::forPageNotFound(); }
        return $this->render('offer_form', $id ? 'Modifier l’offre' : 'Créer une offre', [
            'offer' => $offer, 'requests' => $this->recruitment->approvedRequests($offer['demande_recrutement_id'] ?? null),
            'positions' => $this->recruitment->availablePositions($id),
        ]);
    }

    public function saveOffer() { return $this->hr(fn () => $this->recruitment->saveOffer($this->request->getPost()), 'bloc2/offers'); }
    public function publishOffer(int $id) { return $this->hr(fn () => $this->recruitment->publishOffer($id), 'bloc2/offers'); }
    public function closeOffer(int $id) { return $this->hr(fn () => $this->recruitment->closeOffer($id), 'bloc2/offers'); }
    public function candidates(): string { return $this->render('candidates', 'Répertoire des candidats', ['candidates' => $this->recruitment->candidates((string) $this->request->getGet('q'))]); }

    public function candidateForm(?int $id = null): string
    {
        $candidate = $id ? $this->recruitment->candidate($id) : null;
        if ($id && $candidate === null) { throw PageNotFoundException::forPageNotFound(); }
        return $this->render('candidate_form', $id ? 'Modifier le candidat' : 'Nouveau candidat', ['candidate' => $candidate]);
    }

    public function candidateDetail(int $id): string
    {
        $candidate = $this->recruitment->candidate($id);
        if ($candidate === null) { throw PageNotFoundException::forPageNotFound(); }
        return $this->render('candidate_detail', 'Fiche candidat', ['candidate' => $candidate,
            'applications' => $this->recruitment->applications(['candidat_id' => $id]), 'offers' => $this->recruitment->openOffers()]);
    }

    public function saveCandidate() { return $this->hr(fn () => $this->recruitment->saveCandidate($this->request->getPost()), 'bloc2/candidates'); }
    public function applications(): string { return $this->render('applications', 'Candidatures', [
        'applications' => $this->recruitment->applications($this->request->getGet()), 'candidates' => $this->recruitment->candidates(),
        'offers' => $this->recruitment->allOffers(),
    ]); }
    public function applicationForm(): string { return $this->render('application_form', 'Nouvelle candidature', [
        'candidates' => $this->recruitment->candidates(), 'offers' => $this->recruitment->openOffers(),
        'selectedCandidate' => (int) $this->request->getGet('candidate'),
    ]); }

    public function saveApplication() { return $this->hr(fn () => $this->recruitment->saveApplication($this->request->getPost()), 'bloc2/applications'); }
    public function applicationStatus(int $id) { return $this->hr(fn () => $this->recruitment->changeApplicationStatus($id, $this->request->getPost()), 'bloc2/preselection'); }
    public function preselection(): string { return $this->render('preselection', 'Présélection', ['applications' => $this->recruitment->applicationsForPreselection()]); }
    public function interviews(): string { return $this->render('interviews', 'Planning des entretiens', ['interviews' => $this->recruitment->interviews($this->request->getGet())]); }

    public function interviewForm(?int $id = null): string
    {
        $interview = $id ? $this->recruitment->interview($id) : null;
        if ($id && $interview === null) { throw PageNotFoundException::forPageNotFound(); }
        return $this->render('interview_form', $id ? 'Modifier l’entretien' : 'Planifier un entretien', [
            'interview' => $interview, 'applications' => $this->recruitment->interviewableApplications(),
            'selectedApplication' => (int) $this->request->getGet('application'), 'employees' => $this->recruitment->employees(),
            'selectedParticipants' => $id ? array_column($this->recruitment->interviewParticipants($id), 'id') : [],
        ]);
    }

    public function saveInterview() { return $this->hr(fn () => $this->recruitment->saveInterview($this->request->getPost()), 'bloc2/interviews'); }
    public function interviewStatus(int $id) { return $this->hr(fn () => $this->recruitment->changeInterviewStatus($id, $this->request->getPost()), 'bloc2/interviews'); }
    public function evaluations(?int $id = null): string { return $this->render('evaluations', 'Évaluations', [
        'interviews' => $this->recruitment->completedInterviews(), 'selected' => $id ? $this->recruitment->interview($id) : null,
        'evaluations' => $id ? $this->recruitment->evaluations($id) : [], 'employees' => $id ? $this->recruitment->interviewParticipants($id) : [],
    ]); }
    public function saveEvaluation() { $id = (int) $this->request->getPost('entretien_id'); return $this->hr(fn () => $this->recruitment->saveEvaluation($this->request->getPost()), 'bloc2/evaluations/' . $id); }
    public function decisions(): string { return $this->render('decisions', 'Décision finale', ['applications' => $this->recruitment->decisionFiles()]); }
    public function finalDecision(int $id) { return $this->hr(fn () => $this->recruitment->finalDecision($id, $this->request->getPost()), 'bloc2/decisions'); }
    public function history(): string { return $this->render('history', 'Historique recrutement', [
        'history' => $this->recruitment->history($this->request->getGet()), 'employees' => $this->recruitment->employees(),
    ]); }

    private function render(string $view, string $title, array $data = []): string
    {
        return view('bloc2/' . $view, ['pageTitle' => $title] + $data);
    }
    private function hr(callable $operation, string $fallback)
    {
        return $this->result($operation(), $fallback);
    }
    private function result(array $result, string $fallback)
    {
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);
        if (! $result['ok']) { session()->setFlashdata('errors', $result['errors'] ?? []); session()->setFlashdata('old', $this->request->getPost()); }
        return redirect()->to(site_url($result['redirect'] ?? $fallback));
    }
}
