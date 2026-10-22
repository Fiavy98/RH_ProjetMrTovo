<?php

namespace App\Controllers\Bloc1;

use App\Controllers\BaseController;
use App\Services\Bloc1\DemandeHistoryService;

class HistoryController extends BaseController
{
    protected int $bloc = 1;

    public function list(): string
    {
        return view('bloc1/history_review', [
            'requests' => (new DemandeHistoryService())->requests(),
            'bloc' => $this->bloc,
        ]);
    }

    public function detail(int $id): string
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
