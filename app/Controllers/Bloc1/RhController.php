<?php

namespace App\Controllers\Bloc1;

use App\Controllers\BaseController;
use App\Services\Bloc1\DemandeRhService;

class RhController extends BaseController
{
    protected int $bloc = 1;

    public function detail(int $id): string
    {
        $request = (new DemandeRhService())->request($id);
        if ($request === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('bloc1/rh_detail', ['request' => $request, 'bloc' => $this->bloc]);
    }

    public function decision(int $id)
    {
        $result = (new DemandeRhService())->decide($id, $this->request->getPost());
        session()->setFlashdata($result['ok'] ? 'success' : 'error', $result['message']);

        return redirect()->to(site_url('bloc1/rh-review/' . $id));
    }
}
