<?php

namespace App\Controllers\Bloc1;

use App\Controllers\BlocPageController;
use App\Services\Bloc1\DemandeFinanceService;
use App\Services\Bloc1\DemandeRhService;
use App\Services\Bloc1\DemandeService;
use App\Services\Bloc1\PosteService;

class Bloc1Controller extends BlocPageController
{
    protected int $bloc = 1;

    protected function pages(): DemandeService
    {
        return new DemandeService();
    }

    public function show(string $slug): string
    {
        if ($slug === 'dashboard') {
            return view('bloc1/requests_overview', [
                'requests' => $this->pages()->allRequests(),
                'bloc' => $this->bloc,
            ]);
        }

        if ($slug === 'positions') {
            return view('bloc1/positions', [
                'positions' => (new PosteService())->positions(),
                'bloc' => $this->bloc,
            ]);
        }

        if ($slug === 'rh-review') {
            return view('bloc1/rh_review', [
                'requests' => (new DemandeRhService())->requests(),
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
}
