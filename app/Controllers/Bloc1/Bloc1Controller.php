<?php

namespace App\Controllers\Bloc1;

use App\Controllers\BlocPageController;
use App\Services\BlocPageService;
use App\Services\Bloc1\DemandeFinanceService;
use App\Services\Bloc1\DemandeDirectionService;
use App\Services\Bloc1\DemandeHistoryService;
use App\Services\Bloc1\DemandeRhService;
use App\Services\Bloc1\DemandeService;
use App\Services\Bloc1\PosteService;

class Bloc1Controller extends BlocPageController
{
    protected int $bloc = 1;
    protected function pages(): DemandeService { return new DemandeService(); }

    public function show(string $slug): string
    {
        if ($slug === 'dashboard') {
            return view('bloc1/requests_overview', [
                'requests' => $this->pages()->allRequests(),
                'bloc' => $this->bloc,
            ]);
        }

        if ($slug === 'rh-review') {
            $rh = new DemandeRhService();
            return view('bloc1/rh_review', [
                'requests' => $rh->requests(),
                'screens' => $this->pages()->screens(),
                'bloc' => $this->bloc,
            ]);
        }

        if ($slug === 'finance-review') {
            return view('bloc1/finance_review', [
                'requests' => (new DemandeFinanceService())->requests(),
                'screens' => $this->pages()->screens(),
                'bloc' => $this->bloc,
            ]);
        }

        if ($slug === 'positions') {
            return view('bloc1/positions', [
                'positions' => (new PosteService())->positions(),
                'bloc' => $this->bloc,
            ]);
        }

        $page = $this->pages()->page($slug);

        if ($page === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($slug === 'demande-form') {
            $formData = $this->pages()->formData();

            return view('bloc1/demande_form', [
                'pageTitle' => $page['title'],
                'bloc' => $this->bloc,
                'screens' => $this->pages()->screens(),
                'departments' => $formData['departments'],
                'contracts' => $formData['contracts'],
                'employee' => $formData['employee'],
                'formReady' => $formData['formReady'],
                'formError' => $formData['formError'],
            ]);
        }

        return parent::show($slug);
    }

    public function rhDetail(int $id): string
    {
        $request = (new DemandeRhService())->request($id);
        if ($request === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('bloc1/rh_detail', ['request' => $request, 'bloc' => $this->bloc]);
    }

    public function rhDecision(int $id)
    {
        $result = (new DemandeRhService())->decide($id, $this->request->getPost());
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect()->to(site_url('bloc1/rh-review/' . $id));
    }

    public function financeDetail(int $id): string
    {
        $request = (new DemandeFinanceService())->request($id);
        if ($request === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return view('bloc1/finance_detail', ['request' => $request, 'bloc' => $this->bloc]);
    }

    public function financeDecision(int $id)
    {
        $result = (new DemandeFinanceService())->decide($id, $this->request->getPost());
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect()->to(site_url('bloc1/finance-review/' . $id));
    }

    public function directionList(): string
    {
        return view('bloc1/direction_review', ['requests' => (new DemandeDirectionService())->requests(), 'bloc' => $this->bloc]);
    }

    public function directionDetail(int $id): string
    {
        $service = new DemandeDirectionService();
        $request = $service->request($id);
        if ($request === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return view('bloc1/direction_detail', [
            'request' => $request,
            'validations' => (new DemandeHistoryService())->validations($id),
            'departments' => $service->departments(),
            'bloc' => $this->bloc,
        ]);
    }

    public function directionDecision(int $id)
    {
        $result = (new DemandeDirectionService())->decide($id, $this->request->getPost());
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect()->to(site_url('bloc1/direction-review/' . $id));
    }

    public function createPost(int $id)
    {
        $result = (new DemandeDirectionService())->createPosts($id, $this->request->getPost());
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);
        return redirect()->to(site_url('bloc1/direction-review/' . $id));
    }

    public function historyList(): string
    {
        return view('bloc1/history_review', ['requests' => (new DemandeHistoryService())->requests(), 'bloc' => $this->bloc]);
    }

    public function historyDetail(int $id): string
    {
        $service = new DemandeHistoryService();
        $request = $service->request($id);
        if ($request === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return view('bloc1/history_detail', [
            'request' => $request,
            'validations' => $service->validations($id),
            'bloc' => $this->bloc,
        ]);
    }
}
