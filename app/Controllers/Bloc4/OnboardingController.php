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
        $departmentId = $this->managerDepartmentId();
        return view('bloc4/onboarding/index', [
            'pageTitle' => 'Liste des onboardings',
            'onboardings' => $this->onboardings->liste($this->request->getGet() ?? [], $departmentId),
            'filter' => $this->request->getGet('statut'),
        ]);
    }

    public function show(int $id): string
    {
        $departmentId = $this->managerDepartmentId();
        $onboarding = $this->onboardings->detail($id, $departmentId);
        if ($onboarding === null) throw PageNotFoundException::forPageNotFound();
        return view('bloc4/onboarding/detail', ['pageTitle' => 'Onboarding du salarié', 'onboarding' => $onboarding, 'readOnly' => ui_role() === 'manager']);
    }

    public function save(int $id)
    {
        $result = $this->onboardings->enregistrerTaches($id, $this->request->getPost() ?? []);
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect()->to(site_url('bloc4/onboarding-detail/' . $id));
    }

    private function managerDepartmentId(): ?int
    {
        if (ui_role() !== 'manager') return null;
        $employeeId = (int) session()->get('auth_employee_id');
        $employee = db_connect()->table('employe')->select('departement_id')->where('id', $employeeId)->get()->getRowArray();
        return ! empty($employee['departement_id']) ? (int) $employee['departement_id'] : -1;
    }
}
