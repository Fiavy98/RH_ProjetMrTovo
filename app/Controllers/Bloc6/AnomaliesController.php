<?php

namespace App\Controllers\Bloc6;

use App\Controllers\BaseController;
use App\Services\Bloc6\AnomaliesService;
use CodeIgniter\Exceptions\PageNotFoundException;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

class AnomaliesController extends BaseController
{
    public function index(): string
    {
        $filters = $this->request->getGet() ?? [];
        $timezone = new DateTimeZone('Indian/Antananarivo');
        $filters['date_debut'] ??= (new DateTimeImmutable('first day of this month', $timezone))->format('Y-m-d');
        $filters['date_fin'] ??= (new DateTimeImmutable('today', $timezone))->format('Y-m-d');
        $service = new AnomaliesService();
        $rows = $service->listing($filters);
        return view('bloc6/anomalies/index', [
            'pageTitle' => 'Anomalies de pointage', 'filters' => $filters, 'rows' => $rows,
            'stats' => $service->stats($rows), 'options' => $service->options(),
        ]);
    }

    public function detecter()
    {
        try {
            $count = (new AnomaliesService())->detect((string) $this->request->getPost('date_debut'), (string) $this->request->getPost('date_fin'));
            return redirect()->to(site_url('bloc6/anomalies'))->with('success', $count . ' nouvelle(s) anomalie(s) détectée(s). Les anomalies déjà connues n’ont pas été dupliquées.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function detail(int $id): string
    {
        $row = (new AnomaliesService())->detail($id);
        if (! $row) throw PageNotFoundException::forPageNotFound();
        return view('bloc6/anomalies/detail', ['pageTitle' => 'Traitement d’une anomalie', 'anomalie' => $row]);
    }

    public function traiter(int $id)
    {
        try {
            (new AnomaliesService())->treat($id, (string) $this->request->getPost('statut'), (string) $this->request->getPost('motif'), (int) session()->get('auth_employee_id'));
            return redirect()->to(site_url('bloc6/anomalies/' . $id))->with('success', 'Traitement enregistré sans modification silencieuse du pointage.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
