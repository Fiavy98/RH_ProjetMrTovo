<?php

namespace App\Controllers\Bloc4;

use App\Controllers\BaseController;
use App\Services\Bloc4\OnboardingService;
use CodeIgniter\Exceptions\PageNotFoundException;

class OnboardingController extends BaseController
{
    private OnboardingService $onboardings;

    public function __construct()
    {
        $this->onboardings = new OnboardingService();
    }

    public function index(): string
    {
        return view('bloc4/onboarding/index', [
            'pageTitle' => 'Liste des onboardings',
            'onboardings' => $this->onboardings->liste($this->request->getGet() ?? []),
            'filter' => $this->request->getGet('statut'),
        ]);
    }

    public function show(int $id): string
    {
        $onboarding = $this->onboardings->detail($id);
        if ($onboarding === null) throw PageNotFoundException::forPageNotFound();
        return view('bloc4/onboarding/detail', ['pageTitle' => 'Onboarding du salarié', 'onboarding' => $onboarding]);
    }

    public function save(int $id)
    {
        $result = $this->onboardings->enregistrerTaches($id, $this->request->getPost() ?? []);
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect()->to(site_url('bloc4/onboarding-detail/' . $id));
    }
}
