<?php

namespace App\Controllers\Bloc5;

use App\Services\Bloc5\DossiersService;

class DossiersController extends Bloc5PageController
{
    private DossiersService $service;

    public function __construct()
    {
        $this->service = new DossiersService();
    }

    public function index(): string
    {
        $filters = [
            'q' => trim((string) ($this->request->getGet('q') ?? '')),
            'departement' => trim((string) ($this->request->getGet('departement') ?? '')),
            'statut' => trim((string) ($this->request->getGet('statut') ?? '')),
        ];
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $result = $this->service->liste($filters, $page, 10);

        return view('bloc5/dossiers', [
            'pageTitle' => 'Répertoire des salariés',
            'blockTitle' => self::BLOCK_TITLE,
            'employees' => $result['employees'],
            'departements' => $result['departements'],
            'total' => $result['total'],
            'pagination' => [
                'currentPage' => $result['page'],
                'totalPages' => $result['totalPages'],
                'pages' => $result['pages'],
                'hasPrev' => $result['hasPrev'],
                'hasNext' => $result['hasNext'],
                'start' => $result['start'],
                'end' => $result['end'],
            ],
            'filters' => $filters,
        ]);
    }
}
