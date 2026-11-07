<?php

namespace App\Controllers\Bloc6;

use App\Controllers\BaseController;
use App\Services\Bloc6\Bloc6Service;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Contrôleur du Bloc 6 — Temps de travail et pointage.
 * Réservé exclusivement aux 3 écrans demandés :
 * - Horaires et régimes (/bloc6/horaires)
 * - Planning (/bloc6/planning)
 * - Pointages (/bloc6/pointages)
 */
class Bloc6Controller extends BaseController
{
    private Bloc6Service $service;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->service = new Bloc6Service();
    }

    public function index()
    {
        return redirect()->to(site_url('bloc6/horaires'));
    }

    // =========================================================================
    // 1. HORAIRES ET RÉGIMES DE TRAVAIL (/bloc6/horaires)
    // =========================================================================

    public function horaires(): string
    {
        $filters = $this->request->getGet() ?? [];
        $horaires = $this->service->listeHoraires($filters);
        $stats = $this->service->statsHoraires();

        return view('bloc6/horaires', [
            'pageTitle' => 'Horaires et régimes de travail',
            'horaires'  => $horaires,
            'stats'     => $stats,
            'filters'   => $filters,
        ]);
    }

    public function saveHoraire(?int $id = null)
    {
        $data = $this->request->getPost() ?? [];
        $res = $this->service->enregistrerHoraire($data, $id);

        if (! $res['ok']) {
            session()->setFlashdata('error', $res['message']);
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('success', $res['message']);
        return redirect()->to(site_url('bloc6/horaires'));
    }

    // =========================================================================
    // 2. PLANNING PAR SALARIÉ ET PÉRIODE (/bloc6/planning)
    // =========================================================================

    public function planning(): string
    {
        $filters = $this->request->getGet() ?? [];
        $dateRef = ! empty($filters['date_ref']) ? $filters['date_ref'] : date('Y-m-d');
        
        $planningData = $this->service->planningSemaine($dateRef, $filters);
        $options = $this->service->options();

        return view('bloc6/planning', array_merge($planningData, [
            'pageTitle' => 'Planning par salarié et période',
            'filters'   => $filters,
            'options'   => $options,
        ]));
    }

    public function savePlanning()
    {
        $data = $this->request->getPost() ?? [];
        $currentUser = session()->get('user') ?? [];
        $creePar = isset($currentUser['id']) ? (int) $currentUser['id'] : null;

        $res = $this->service->affecterPlanning($data, $creePar);

        if (! $res['ok']) {
            session()->setFlashdata('error', $res['message']);
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('success', $res['message']);
        $dateRef = $data['date_debut'] ?? date('Y-m-d');
        return redirect()->to(site_url('bloc6/planning?date_ref=' . $dateRef));
    }

    // =========================================================================
    // 3. ENTRÉES, SORTIES, RETARDS ET HEURES SUPPLÉMENTAIRES (/bloc6/pointages)
    // =========================================================================

    public function pointages(): string
    {
        $filters = $this->request->getGet() ?? [];
        $date = ! empty($filters['date']) ? $filters['date'] : date('Y-m-d');

        $pointages = $this->service->listePointages($filters);
        $stats = $this->service->statsPointages($date);
        $options = $this->service->options();
        $employesPlanifies = $this->service->employesPlanifies($date);
        $planningsParDate = $this->service->planningsActifs();

        return view('bloc6/pointages', [
            'pageTitle'         => 'Entrées, sorties et pointages',
            'pointages'         => $pointages,
            'stats'             => $stats,
            'filters'           => $filters,
            'curDate'           => $date,
            'options'           => $options,
            'employesPlanifies' => $employesPlanifies,
            'planningsParDate'  => $planningsParDate,
        ]);
    }

    public function savePointage()
    {
        $data = $this->request->getPost() ?? [];
        $currentUser = session()->get('user') ?? [];
        $modifiePar = isset($currentUser['id']) ? (int) $currentUser['id'] : null;

        $res = $this->service->enregistrerPointage($data, $modifiePar);

        if (! $res['ok']) {
            session()->setFlashdata('error', $res['message']);
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('success', $res['message']);
        $date = $data['date_pointage'] ?? date('Y-m-d');
        return redirect()->to(site_url('bloc6/pointages?date=' . $date));
    }
}
