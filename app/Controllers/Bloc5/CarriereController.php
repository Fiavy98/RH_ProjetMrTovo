<?php

namespace App\Controllers\Bloc5;

use App\Services\Bloc5\CarriereService;
use CodeIgniter\Exceptions\PageNotFoundException;

class CarriereController extends Bloc5PageController
{
    private CarriereService $service;

    public function __construct()
    {
        $this->service = new CarriereService();
    }

    public function index(?int $employeeId = null): string
    {
        $search = trim((string) ($this->request->getGet('q') ?? ''));
        $dateDebut = trim((string) ($this->request->getGet('date_debut') ?? ''));
        $dateFin = trim((string) ($this->request->getGet('date_fin') ?? ''));

        $period = $this->service->validateDateRange($dateDebut, $dateFin);

        if ($employeeId === null) {
            return view('bloc5/carriere', [
                'pageTitle' => 'Carrière et rémunération',
                'blockTitle' => self::BLOCK_TITLE,
                'employee' => null,
                'contracts' => [],
                'affectations' => [],
                'salaryHistory' => [],
                'search' => $search,
                'searchResults' => $this->service->searchEmployees($search),
                'dateDebut' => $period['start'],
                'dateFin' => $period['end'],
                'message' => session()->getFlashdata('message'),
                'error' => $period['ok'] ? session()->getFlashdata('error') : $period['message'],
            ]);
        }

        $employee = $this->service->employee($employeeId);
        if ($employee === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('bloc5/carriere', [
            'pageTitle' => 'Carrière et rémunération',
            'blockTitle' => self::BLOCK_TITLE,
            'employee' => $employee,
            'contracts' => $this->service->contractsForEmployee($employeeId, $period['start'], $period['end']),
            'affectations' => $this->service->affectationsForEmployee($employeeId, $period['start'], $period['end']),
            'salaryHistory' => $this->service->historiqueSalaireForEmployee($employeeId, $period['start'], $period['end']),
            'search' => '',
            'searchResults' => [],
            'dateDebut' => $period['start'],
            'dateFin' => $period['end'],
            'message' => session()->getFlashdata('message'),
            'error' => $period['ok'] ? session()->getFlashdata('error') : $period['message'],
        ]);
    }
}
