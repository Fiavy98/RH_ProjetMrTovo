<?php

namespace App\Controllers\Bloc5;

use App\Services\Bloc5\DossierService;
use CodeIgniter\Exceptions\PageNotFoundException;

class DossierController extends Bloc5PageController
{
    private DossierService $service;

    public function __construct()
    {
        $this->service = new DossierService();
    }

    public function index(?int $id = null): string
    {
        if ($id !== null) {
            return $this->show($id);
        }

        return view('bloc5/dossier', [
            'pageTitle' => 'Dossier administratif',
            'blockTitle' => self::BLOCK_TITLE,
            'employee' => null,
            'message' => 'Sélectionnez un salarié pour afficher son dossier administratif.',
        ]);
    }

    public function show(int $id): string
    {
        $employee = $this->service->employee($id);
        if ($employee === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('bloc5/dossier', [
            'pageTitle' => 'Dossier administratif',
            'blockTitle' => self::BLOCK_TITLE,
            'employee' => $employee,
            'message' => null,
        ]);
    }
}
