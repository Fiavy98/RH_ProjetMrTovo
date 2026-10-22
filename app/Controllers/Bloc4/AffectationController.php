<?php

namespace App\Controllers\Bloc4;

use App\Controllers\BaseController;
use App\Services\Bloc4\AffectationSimulationService;

class AffectationController extends BaseController
{
    private AffectationSimulationService $affectations;

    public function __construct()
    {
        $this->affectations = new AffectationSimulationService();
    }

    public function index(): string
    {
        return view('bloc4/affectations/index', ['pageTitle' => 'Gestion des affectations'] + $this->affectations->pageData());
    }

    public function save()
    {
        $result = $this->affectations->enregistrer($this->request->getPost() ?? []);
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect()->to(site_url('bloc4/assignment-b4'));
    }
}
