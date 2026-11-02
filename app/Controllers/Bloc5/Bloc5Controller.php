<?php

namespace App\Controllers\Bloc5;

use App\Controllers\BaseController;
use App\Services\Bloc5\Bloc5Service;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/** Contrôleur de navigation statique du Bloc 5. */
class Bloc5Controller extends BaseController
{
    private Bloc5Service $pages;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->pages = new Bloc5Service();
    }

    public function index()
    {
        return redirect()->to(site_url('bloc5/dossiers'));
    }

    public function show(string $slug): string
    {
        $screen = $this->pages->screen($slug);
        if ($screen === null) throw PageNotFoundException::forPageNotFound();

        return view('shared/module_scaffold', [
            'pageTitle' => $screen['title'],
            'blockTitle' => 'Bloc 5 · Dossier administratif du salarié',
            'screen' => $screen,
            'screens' => $this->pages->screens(),
        ]);
    }
}
