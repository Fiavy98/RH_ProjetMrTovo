<?php

namespace App\Controllers\Bloc6;

use App\Controllers\BaseController;
use App\Services\Bloc6\TempsTravailleService;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

class TempsTravailleController extends BaseController
{
    public function index(): string
    {
        $filters = $this->request->getGet() ?? [];
        $timezone = new DateTimeZone('Indian/Antananarivo');
        $filters['date_debut'] ??= (new DateTimeImmutable('first day of this month', $timezone))->format('Y-m-d');
        $filters['date_fin'] ??= (new DateTimeImmutable('today', $timezone))->format('Y-m-d');
        $service = new TempsTravailleService();
        $rows = [];
        $error = null;
        try {
            $rows = $service->summary($filters['date_debut'], $filters['date_fin'], $filters);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
        return view('bloc6/temps_travaille', [
            'pageTitle' => 'Temps réellement travaillé', 'filters' => $filters, 'rows' => $rows,
            'options' => $service->options(), 'calculationError' => $error,
        ]);
    }
}
