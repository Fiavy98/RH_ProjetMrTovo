<?php

namespace App\Controllers\Bloc1;

use App\Controllers\BaseController;
use App\Services\Bloc1\DemandeDirectionService;
use App\Services\Bloc1\DemandeHistoryService;

class DirectionController extends BaseController
{
    protected int $bloc = 1;

    public function list(): string
    {
        return view('bloc1/direction_review', [
            'requests' => (new DemandeDirectionService())->requests(),
            'bloc' => $this->bloc,
        ]);
    }

    public function detail(int $id): string
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

    public function decision(int $id)
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
}
