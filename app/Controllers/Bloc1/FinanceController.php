<?php

namespace App\Controllers\Bloc1;

use App\Controllers\BaseController;
use App\Services\Bloc1\DemandeFinanceService;

class FinanceController extends BaseController
{
    protected int $bloc = 1;

    public function detail(int $id): string
    {
        $request = (new DemandeFinanceService())->request($id);
        if ($request === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('bloc1/finance_detail', ['request' => $request, 'bloc' => $this->bloc]);
    }

    public function decision(int $id)
    {
        $result = (new DemandeFinanceService())->decide($id, $this->request->getPost());
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);

        return redirect()->to(site_url('bloc1/finance-review/' . $id));
    }
}
